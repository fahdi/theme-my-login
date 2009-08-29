<?php

$version = ( isset($_GET['ver']) ) ? $_GET['ver'] : '';

header("Content-type: text/css");

?>

#container { margin: 20px 0 5px 0; font-size: 12px; }

/*
#container ul { list-style: none; position: relative; }
#container li { position: relative; display: inline; }
#container li a { float: left; text-decoration: none; padding: .5em 1em; }

#container h3 { clear: both; }
*/

#container table input.regular-text { width: 25em; }
#container table input.extended-text { width: 40em; }
#container table input.full-text { width: 99%; }
#container table input.small-text { width: 50px; }

#container div div { font-size: 1em; }

<?php if ( version_compare($version, '2.8', '>=') ) : ?>

<?php elseif ( version_compare($version, '2.7', '>=') ) : ?>

<?php elseif ( version_compare($version, '2.5', '>=') ) : ?>

.tabs {
    height: auto;
    max-height: auto;
    }
    
.tabs .tabs-div {
    height: auto;
    max-height: auto;
    margin-left: 0;
    }
    
.tabs .tabs-div .tabs-div {
    height: auto;
    max-height: auto;
    overflow: visible;
    }

<?php endif; ?>
