<?php

return [
    [['GET', 'POST'], '/company', ['Controllers\EmployeeController', 'display']],
    [['GET', 'POST'], '/employee', ['Controllers\EmployeeController', 'display']],
    ['GET', 'barcoms/index.php', ['Controllers\Homepage', 'display']],
    ['GET', 'barcoms/', ['Controllers\Homepage', 'display']],
    [['GET', 'POST'], '/login', ['Controllers\Login', 'show']],
    [['GET', 'POST'], '/logout', ['Controllers\Login', 'logout']],
];