<?php
$check_pdf = $_GET['pdf'];
if (isset($check_pdf)) {
    
    if($check_pdf === "China Gold Park Mall Residency.pdf"){
        header("Content-disposition: attachment; filename={$check_pdf}");
        header("Content-type: application/pdf");
        readfile("{$check_pdf}");
        exit();
    }
}

?>