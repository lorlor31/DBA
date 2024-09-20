<?php

function hmenu_remove_http($url) {
   $disallowed = array('http://', 'https://', 'ftp://', '//', '\\\\','http:\\\\', 'https:\\\\', 'ftp:\\\\');
   foreach($disallowed as $d) {
      if(strpos($url, $d) === 0) {
         return str_replace($d, '', $url);
      }
   }
   return $url;
}

?>