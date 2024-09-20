<?php

class WC_TrackingTableParser {

    /**
     * Single instance of WC_TrackingTableImport
     * @var 	object
     * @access  private
     */
    private static $_instance = null;

    /**
     * Csv file
     * @var 	object
     * @access  private
     */
    private $file;

    /**
     * File upload name
     * @var 	string
     * @access  private
     */
    private $filename;

    /**
     * File upload date
     * @var 	DateTime
     * @access  private
     */
    private $filedate;

    /**
     * Authorized upload file
     * @var 	boolean
     * @access  private
     */
    private $allowed_upload = true;

    /**
     * Authorized extensions
     * @var 	array
     * @access  private
     */
    private $allowed_filetypes = array('.csv');

    /**
     * Maximum file size
     * @var 	int
     * @access  private
     */
    private $max_filesize = 5000000;

    /**
     * Full directory upload
     * @var 	string
     * @access  private
     */
    private $upload_dir;

    /**
     * List orders modified by parser
     * @var 	array
     * @access  private
     */
    private $orders = [];

    /**
     * List prefix to excluded by parser
     * @var 	array
     * @access  private
     */
    private $excluded_prefix = ['CFDB'];

    /**
     * Date format in csv file
     * @var 	string
     * @access  private
     */
    private $date_format = 'd/m/Y';

    /**
     * Date offset in days
     * @var 	int
     * @access  private
     */
    private $date_offset = 1;

    /**
     * Count columns in csv
     * @var int
     * @access  private
     */
    private $number_columns = 0;

	public function __construct() {
	    $this->file = $_FILES['i'];
        $filename = $this->file['name'];
        // Check file extension
        $name = strtolower(substr($filename, 0, strpos($filename,'.')));
        $extension = substr($filename, strpos($filename,'.'), strlen($filename)-1);
        if ( !in_array($extension, $this->allowed_filetypes) ) {
            WC_TrackingTable::notify('Extension du fichier ' . $extension . ' non autorisée', 'error');
            $this->allowed_upload = false;
            return;
        }
        // Check file size
        $filesize = filesize($this->file['tmp_name']);
        if ( $filesize > $this->max_filesize) {
            WC_TrackingTable::notify('Taille du fichier ' . intval($filesize / 1000) . ' Ko dépasse la limite autorisée', 'error');
            $this->allowed_upload = false;
            return;
        }
        // Check directory and filename
        $this->upload_dir = WCTT_UPLOADS;
        $sanitize = sanitize_file_name($name);
        $this->filename = $sanitize . $extension;
        // Copy directory upload
        if ( !is_dir($this->upload_dir) ) {
            mkdir($this->upload_dir, 0755);
        }
        // Copy file upload
        if ( !move_uploaded_file($this->file['tmp_name'], $this->upload_dir . DSP . $this->filename) ) {
            WC_TrackingTable::notify('Erreur lors de l\'importation du fichier ' . $this->filename, 'error');
            $this->allowed_upload = false;
        } else {
            WC_TrackingTable::notify('Importation du fichier ' . $this->filename . ' terminé', 'info');
        }
	}

    public function parser() {
        if ( !$this->allowed_upload ) {
            return false;
        }
        // Load csv and map column
        $file = fopen($this->upload_dir . DSP . $this->filename, 'r');
        $delimiter = $this->detectDelimiter($file);
        if ( is_null($delimiter) ) {
            WC_TrackingTable::notify('Problème dans la recherche du délimiteur CSV', 'error');
            return false;
        }
        while ( ($line = fgetcsv($file, 0, $delimiter)) !== false ) {
            if ( $this->number_columns == 5 ) {
                list($date_maq[], $ref_vinco[], $ref_aplus[], $status_fdr[], $date_fdr[]) = array_map('trim', $line);
            } elseif ( $this->number_columns == 2 ) {
                list($ref_aplus[], $report[]) = array_map('trim', $line);
            }
        }
        // Run parser
        $list_not_found = $list_review = '';
        require_once WCTT_PATH . 'includes/class-wc-tracking-table-alert.php';
        for ( $i = 0; $i < sizeof($ref_aplus); $i++ ) {
            // Check order format
            preg_match('/[0-9]/', $ref_aplus[$i], $matches, PREG_OFFSET_CAPTURE);
            $prefix = substr($ref_aplus[$i], 0, $matches[0][1]);
            $number = substr($ref_aplus[$i], $matches[0][1], 5);
            $number = substr($number, 0, 1) == '0' ? substr($number, 1) : $number;
            if ( !in_array($prefix, $this->excluded_prefix) && !preg_match('/[a-zA-Z.\-\/]/', $number) ) {
                // Retrieve order
                $status = $this->number_columns == 5 ? array('wc-processing', 'wc-partial-shipped') : array('wc-completed');
                $post = get_posts( array(
                    'numberposts'  => 1,
                    'meta_key'     => '_ywson_custom_number_order_complete',
                    'meta_value'   => $number,
                    'meta_compare' => 'LIKE',
                    'post_type'    => 'shop_order',
                    'post_status'  => $status
                ) );
                if ( sizeof($post) > 0 ) {
                    if ( $this->number_columns == 5 ) {
                        // execute portefeuille
                        $this->orders[] = wc_get_order($post[0]->ID);
                        // ref vinco
                        update_post_meta( $post[0]->ID, WC_TrackingTable::$meta_vinco, $ref_vinco[$i] );
                        // check date
                        if ( empty($date_fdr[$i]) ) {
                            delete_post_meta( $post[0]->ID, WC_TrackingTable::$meta_process );
                            $date = DateTime::createFromFormat($this->date_format, $date_maq[$i]);
                        } else {
                            update_post_meta( $post[0]->ID, WC_TrackingTable::$meta_process, 1 );
                            $date = DateTime::createFromFormat($this->date_format, $date_fdr[$i]);
                        }
                        $date->add(new DateInterval('P'.$this->date_offset.'D'));
                        update_post_meta( $post[0]->ID, WC_TrackingTable::$meta_date, $date->format($this->date_format) );
                        // check blocked
                        if ( $status_fdr[$i] == 'O' ) {
                            update_post_meta( $post[0]->ID, WC_TrackingTable::$meta_blocked, 1 );
                        } else {
                            delete_post_meta( $post[0]->ID, WC_TrackingTable::$meta_blocked );
                        }
                    } elseif ( $this->number_columns == 2 ) {
                        // execute reporting
                        $this->orders[] = wc_get_order($post[0]->ID);
                        // content to search
                        $compliant_text = 'Livraison conforme';
                        $litigation_text = 'Livraison avec réserve';
                        // check order
                        if ( strpos($report[$i], $compliant_text) !== false ) {
                            update_post_meta( $post[0]->ID, WC_TrackingTable::$meta_delivery, 1 );
                            if ( strpos(get_site_url(), 'staging') !== false &&
                                empty(get_post_meta( $post[0]->ID, WC_TrackingTable::$meta_review, true )) &&
                                WC_TrackingTableAlert::instance()->send_review_by_cron($post[0]->ID) ) {
                                $list_review .= $ref_aplus[$i] . ', ';
                            }
                        } elseif ( strpos($report[$i], $litigation_text) !== false ) {
                            update_post_meta( $post[0]->ID, WC_TrackingTable::$meta_delivery, 1 );
                            update_post_meta( $post[0]->ID, WC_TrackingTable::$meta_follow, 2 );
                        } elseif ( !empty($report[$i]) ) {
                            $note = get_post_meta( $post[0]->ID, WC_TrackingTable::$meta_note, true );
                            if ( empty($note) ) {
                                $note = '[MAJ SUIVI]<br>' . $report[$i];
                            } elseif ( strpos($note, $report[$i]) === false ) {
                                $note = '[MAJ SUIVI]<br>' . $report[$i] . '<br><br>' . $note;
                            }
                            update_post_meta( $post[0]->ID, WC_TrackingTable::$meta_note, $note );
                        }
                    }
                } else {
                    $list_not_found .= $ref_aplus[$i] . ', ';
                }
            } else {
                $list_not_found .= $ref_aplus[$i] . ', ';
            }
        }
        // Return message
        $list_review = strlen($list_review) > 0 ? substr($list_review, 0, -2) : '';
        $list_not_found = strlen($list_not_found) > 0 ? substr($list_not_found, 0, -2) : '';
        if (sizeof($this->orders) == 0) {
            WC_TrackingTable::notify('Aucune commande trouvée dans le fichier csv importé', 'info');
        } else {
            $s = sizeof($this->orders) > 1 ? 's' : '';
            WC_TrackingTable::notify(sizeof($this->orders) . ' commande'.$s.' trouvée'.$s.' sur ' . sizeof($ref_aplus) . ' dans le fichier csv importé', 'info');
            if ( $this->number_columns == 2 ) {
                WC_TrackingTable::notify('Demande d\'avis envoyée : ' . $list_review, 'info');
            }
            WC_TrackingTable::notify('Commande non trouvée ou ignorée : ' . $list_not_found, 'info');
        }
        fclose($file);
        return true;
    }

    public function getOrders() {
	    return $this->orders;
    }

    public function validateDate($date, $format = 'd-m-y'){
        $d = DateTime::createFromFormat($format, $date);
        return $d && $d->format($format) === $date;
    }

    private function detectDelimiter( $file ) {
        $delimiters = array( ',' => 0, ';' => 0, "\t" => 0, '|' => 0 );
        $firstLine = '';
        if ( $file ) {
            $firstLine = fgets($file);
        }
        if ( $firstLine ) {
            foreach ($delimiters as $delimiter => &$count) {
                $count = count(str_getcsv($firstLine, $delimiter));
            }
            $this->number_columns = max($delimiters);
            return array_search(max($delimiters), $delimiters);
        } else {
            return null;
        }
    }

    public static function instance () {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

}
