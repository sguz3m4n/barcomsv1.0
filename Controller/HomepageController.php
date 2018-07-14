<?php

namespace Controllers;

require __DIR__ . "/base_template.php";

class Homepage extends LoggedInController {

    //public static $Menu;
    private $menu;
    private $datamanager;
    private $companymenu;
    private $employeemenu;
    private $adminmenu;

    public function show($params) {
        //Get username for display
        if (isset($_SESSION['login_user'])) {
            $username = $_SESSION['login_user'];
        } else {
            $username = '';
        }
        if (isset($_SESSION['user_roles'])) {
            $role = $_SESSION['user_roles'];
        } else {
            $role = '';
        }
        //Redirect to user specific homepage
        $roleMap = [
            'Officer' =>'/officer',
            'Payment Clerk' =>'/createpayment',
            'Receipt Clerk' =>'/createdeposit',
            'Reconciliation Clerk' =>'/reports',
            'Human Resource Clerk' =>'/employee/create',
            ''=>'/login'
        ];
        
        if (isset($roleMap[$role])){
            $destination = $roleMap[$role];
           // flush();
            header('Location:'.$destination);
            die();    
        }
        //Admin Permission Module Permissions
        $CreateUser = $ManageTravel = $ManageOT = $ManageOther = array('Administrator', 'Super User', 'Manager');
        //CRUD Company Module Permissions
        $CreateCompany = $ViewCompany = $EditCompany = $DeleteCompany = array('Manager', 'Administrator', 'Super User');
        //CRUD Company Module Permissions
        //CRU Employee Module Permissions
        $CreateEmployee = $ViewEmployee = $EditEmployee = array('Human Resource Clerk', 'Manager', 'Administrator', 'Super User');
        //CRU Employee Module Permissions
        //Delete Employee Module Permissions
        $DeleteEmployee = array('Manager', 'Administrator', 'Super User');
        //Delete Employee Module Permissions
        //Create Deposit Module Permissions
        $CreateDeposit = array('Receipt Clerk', 'Manager', 'Super User', 'Administrator');
        //Create Deposit Module Permissions        
        //Create Refund Module Permissions
        $CreateRefund = array('Manager', 'Super User', 'Administrator');
        //Create Refund Module Permissions
        ////Bill Payment Module Permissions
        $BillPayment = array('Payment Clerk', 'Manager', 'Super User', 'Administrator');
        //Bill Payment Module Permissions
        //Generate PRN Module Permissions
        $PRNGenerate = array('Payment Clerk', 'Manager', 'Super User', 'Administrator');
        //Generate PRN Module Permissions
        //View/Generate Reports Module Permissions
        $Reporting = array('Manager', 'Reconciliation Clerk', 'Administrator', 'Super User', 'Payment Clerk');

        $DataManager = array('Human Resource Clerk', 'Manager', 'Administrator', 'Super User', 'Admin');

        $inherited = new BaseTemplate();
        $inherited->load("Views/content.html");
        $content = $inherited->template;

        $template = new MasterTemplate();
        $template->load("Views/home.html");
        $template->replace('username', $username);

        if (in_array($role, $DataManager)) {
            $datamanager = '<li class="dropdown dropdown-fw dropdown-fw-disabled">
                                    <a class="text-uppercase" href="javascript:;">
                                       <i class="glyphicon glyphicon-open-file" style="font-size: 15px"></i>Data Management </a>
                                    <ul class="dropdown-menu dropdown-menu-fw">
                                        <li class="dropdown more-dropdown-sub ">
                                            <a href="javascript:;"> Company Manager </a>
                                            <ul class="dropdown-menu">
                                                { companymenu }
                                            </ul>
                                        </li>
                                        <li class="dropdown more-dropdown-sub ">
                                            <a href="javascript:;"> Employee Manager </a>
                                            <ul class="dropdown-menu">
                                                { employeemenu }
                                            </ul>
                                        </li>
                                        <li class="dropdown">
                                            <a href="/adminconsole"> Admin Console </a>
                                          
                                        </li>
                                    </ul>
                                </li>';

            $this->datamanager = $datamanager;
        }

        if (in_array($role, $CreateCompany)) {
            $createitem = '<li><a href="/company/create">Create Company</a></li>';
            $this->companymenu = $this->companymenu . $createitem;
        }
    
        if (in_array($role, $EditCompany)) {
            $edititem = '<li><a href="/company/edit">View/Edit/Delete Company Details</a></li>';
            $this->companymenu = $this->companymenu . $edititem;
        }

        if (in_array($role, $CreateEmployee)) {
            $createitem = '<li><a href="/employee/create">Create Employee</a></li>';
            $this->employeemenu = $this->employeemenu . $createitem;
        }

        if (in_array($role, $EditEmployee)) {
            $edititem = '<li><a href="/employee/edit">View/Edit/Delete Employee Details</a></li>';
            $this->employeemenu = $this->employeemenu . $edititem;
        }

        if (in_array($role, $CreateUser)) {
            $createitem = '<li><a href="/admin/edituser">Manage Admin User</a></li>';
            $this->adminmenu = $this->adminmenu . $createitem;
        }

        if (in_array($role, $ManageTravel)) {
            $manageitem = ' <li><a href="/admin/managetravel">Manage Travel Rates</a></li>';
            $this->adminmenu = $this->adminmenu . $manageitem;
        }

        if (in_array($role, $ManageOT)) {
            $manageotitem = '<li><a href="/admin/managepot">Manage OT Rates</a></li>';
            $this->adminmenu = $this->adminmenu . $manageotitem;
        }

        if (in_array($role, $ManageOther)) {
            $manageotheritem = '<li><a href="/admin/manageother">Manage Other Rates</a></li>';
            $this->adminmenu = $this->adminmenu . $manageotheritem;
        }

        if (in_array($role, $CreateDeposit)) {
            $deposititem = '<li>
                                    <a class="text-uppercase" href="/createdeposit">Client Deposit</a>
                                </li>';
            $this->menu = $this->menu . $deposititem;
        }

        if (in_array($role, $CreateRefund)) {
            $refunditem = '<li>
                                    <a class="text-uppercase" href="/createrefund">Client Refund</a>
                                </li>';
            $this->menu = $this->menu . $refunditem;
        }

        if (in_array($role, $BillPayment)) {
            $billitem = ' <li >
                                    <a class="text-uppercase" href="/createpayment">Make Payment</a>
                                </li> ';
            $this->menu = $this->menu . $billitem;
        }

        if (in_array($role, $PRNGenerate)) {
            $prnitem = '<li>
                                    <a class="text-uppercase" href="/disburseprn">Generate PRN</a>
                                </li> '
            ;
            $this->menu = $this->menu . $prnitem;
        }

        if (in_array($role, $Reporting)) {
            $prnitem = ' <li>
                                  
                                    <a class="text-uppercase" onclick="openNav()">Reports</a>
                                </li>'
            ;
            $this->menu = $this->menu . $prnitem;
        }
        $template->replace('datamanager', $this->datamanager);
        $template->replace('companymenu', $this->companymenu);
        $template->replace('employeemenu', $this->employeemenu);
        $template->replace('adminmenu', $this->adminmenu);
        $template->replace('menu', $this->menu);
        $template->replace('page_content', $content);
        $template->publish();
    }

}
