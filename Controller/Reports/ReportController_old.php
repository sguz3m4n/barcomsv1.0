<?php
namespace Controllers;

require 'Controller/base_template.php';

use Jaspersoft\Client\Client;
  
class ReportController extends PermissionController {
    function __construct() {
        $this->setRoles(['Managers']);
    }
    
    function show($params){
        try {
            $format = array_key_exists('format',$params)?$params['format']:'html';
            $name = array_key_exists('name',$params)?$params['name']:'summary';
            $c = new Client(
                                   "http://localhost:8080/jasperserver",
                                   "jasperadmin",
                                   "jasperadmin"
                           );
            $c->setRequestTimeout(60);
            $report = $c->reportService()->runReport('/reports/'.$name, $format);
            
            if($format == 'html') {
                header('Content-Type: text/html');
            }
             elseif($format == 'pdf') {
                header('Cache-Control: must-revalidate');
                header('Pragma: public');
                header('Content-Description: File Transfer');
                header('Content-Disposition: attachment; filename='.$name.'.pdf');
                header('Content-Transfer-Encoding: binary');
                header('Content-Length: ' . strlen($report));
                header('Content-Type: application/pdf');
                echo $report;
                exit();
            
            }
            elseif($format == 'xlsx') {
                header('Cache-Control: must-revalidate');
                header('Pragma: public');
                header('Content-Description: File Transfer');
                header('Content-Disposition: attachment; filename='.$name.'.xlsx');
                header('Content-Transfer-Encoding: binary');
                header('Content-Length: ' . strlen($report));
                header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
                echo $report;
                exit();
            }
            else {
                $report = "Invalid report format";
            }
            
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