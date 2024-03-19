<?php
function ta($in) {
    if(TESTMODUS>0) {
        echo('<pre class="ta">');
        print_r($in);
        echo('</pre>');
    }
}
?>