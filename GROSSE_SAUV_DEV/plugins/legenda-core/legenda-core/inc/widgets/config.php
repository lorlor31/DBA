<?php 
	function etheme_widget_label( $label, $id ) {
	    echo "<label for='{$id}'>{$label}</label>";
	}
	function etheme_widget_input_checkbox( $label, $id, $name, $checked, $value = 1 ) {
	    echo "\n\t\t\t<p>";
	    echo "<label for='{$id}'>";
	    echo "<input type='checkbox' id='{$id}' value='{$value}' name='{$name}' {$checked} /> ";
	    echo "{$label}</label>";
	    echo '</p>';
	}
	function etheme_widget_textarea( $label, $id, $name, $value ) {
	    echo "\n\t\t\t<p>";
	    etheme_widget_label( $label, $id );
	    echo "<textarea id='{$id}' name='{$name}' rows='3' cols='10' class='widefat'>" . strip_tags( $value ) . "</textarea>";
	    echo '</p>';
	}
	function etheme_widget_input_text( $label, $id, $name, $value ) {
	    echo "\n\t\t\t<p>";
	    etheme_widget_label( $label, $id );
	    echo "<input type='text' id='{$id}' name='{$name}' value='" . strip_tags( $value ) . "' class='widefat' />";
	    echo '</p>';
	}
	function etheme_admin_widget_preview($name) {
		if (isset($_GET['legacy-widget-preview'])){
				echo '<div class="et-no-preview"><h3>8theme - '. $name .'</h3><p>No preview available.</p></div>';
		echo '<style>
	            .et-no-preview{
	                font-size: 13px;
	                background: #f0f0f0;
	                padding: 8px 12px;
	                color: #000;
	                font-family: -apple-system,BlinkMacSystemFont,Segoe UI,Roboto,Oxygen-Sans,Ubuntu,Cantarell,Helvetica Neue,sans-serif;
	            }
	            .et-no-preview h3 {
	                font-size: 14px;
	                font-family: inherit;
	                font-weight: 600;
	                margin: 4px 0;
	            }
	            .et-no-preview p {
	                margin: 4px 0;
	                font-size: 13px;
	            }
	        </style>';
		} else {
			return false;
		}
	}
?>