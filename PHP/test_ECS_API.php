<?php
include('ECS_API.php');
$community = '/xxx/xxx';
$host = "xx";

//GET LIST MEMBERS
$obj = new Helper_ECS_API($host, $community, 'list-members');
var_dump($obj->execute());

echo '<hr>';

//ADD NEW MEMBER
$obj = new Helper_ECS_API($host, $community, 'add-member');
$data = array();
$data["email"]       	= 'email_address@address.com';
$data["firstName"]    = '';
$data["lastName"]   	= '';
$data["country"]      = '';
$data["title"]			  = '';
$data["organization"]	= '';
$data["telephone"]		= '';
$data["fax"]			    = '';

$obj->add_new_member($data);
var_dump($obj->get_header()); //HTTP/1.1 200 OK == record was added successfully

