<?php
namespace Controllers;
require 'Controller/base_template.php';

use Jaspersoft\Client\Client;
  
class PaymentsReportController extends PermissionController {
    function __construct() {
        $this->setRoles(['Payment Clerk','Administrator','Manager','Super User']);
    }
    
    function show($params){
        try {
            $format = array_key_exists('format',$params)?$params['format']:'html';
            $name = array_key_exists('name',$params)?$params['name']:'summary';
            $param = array_key_exists('parameter',$params)?$params['parameter']:NULL;
            $JASPER_URL = "http://www.pot.customs.gov.bb:8080/jasperserver/";
            //$JASPER_URL = "http://localhost:8080/jasperserver/";
            $USER = "jasperadmin";
            $PASS = "jasperadmin";
            // For Iframe
           $report = $JASPER_URL . 'flow.html?_flowId=viewReportFlow&_flowId=viewReportFlow&ParentFolderUri=%2Freports&reportUnit=%2Freports%2F'.$name.'&standAlone=true&decorate=no&output=html&j_username='.$USER.'&j_password='.$PASS;
        } catch (Exception $ex) {
            echo 'RESTRequestException:';
            echo 'Exception message:   ',  $e->getMessage(), "\n";
            echo 'Set parameters:      ',  $e->parameters, "\n";
            echo 'Expected status code:',  $e->expectedStatusCodes, "\n";
            echo 'Error code:          ',  $e->errorCode, "\n";
            $report = "We've encountered an error generating the report, try again later";
        }
        
        $template = new BaseTemplate();
        $template->load('Views/Reports/report.html');
        $template->replace('report',$report);
        $template->replace('report_name', $name);
        $template->publish();

    }
}