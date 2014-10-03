<?php
/*
 * “úŽŸˆ—
 */
require_once(realpath(dirname(__FILE__) . "/../") . "/lib/config.php");
require_once("dbaccess.php");

$inst = DBConnection::getConnection($DB_URI);

$ymd = date('Y-m-d', time() - (31 * 24 * 3600));

$sql = "update nailist set ranking=0";
$inst->db_exec($sql);
$sql = "update nailist set ranking=(select count(*) from access where kind=1 and item_id=nailist_id and date(reg_date)>'{$ymd}')";
$inst->db_exec($sql);

$sql = "update catalog set ranking=0";
$inst->db_exec($sql);
$sql = "update catalog set ranking=(select count(*) from access where kind=1 and item_id=catalog.seq and date(reg_date)>'{$ymd}')";
$inst->db_exec($sql);

?>