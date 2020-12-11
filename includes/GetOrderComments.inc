<?php

function getOrderCommentsTable($orderno, $orderlineno, &$db, $ids = array()) {
	
	$filter = "";
	if(empty($ids)) {
		$filter = " AND orderlineno = '$orderlineno'";
	} else {
		$filter = " AND id IN (" . implode(',', $ids) . ")";
	}
	
	$SQL = "
		SELECT
		ordercomments.userid,
		ordercomments.comment,
		DATE_FORMAT(ordercomments.date, '%d-%m-%Y %h:%m:%s') AS date,
		www_users.realname
		FROM ordercomments
		LEFT JOIN www_users
		ON www_users.userid = ordercomments.userid
		WHERE orderno = '$orderno'
		$filter
		ORDER BY id DESC
	";
	
	$rs = DB_query($SQL, $db);

	$commentsHTML = "";

	if(DB_num_rows($rs) >= 0) {
	
		$i = 1;
	
		$commentsHTML .= "<table style='text-align:left; width:100%'>";
		$commentsHTML .= "<tr>";
		$commentsHTML .= "<th style='width:10%'>" . _('USUARIO') . "</th>";
		$commentsHTML .= "<th style='width:60%'>" . _('COMENTARIO') . "</th>";
		$commentsHTML .= "<th style='width:30%'>" . _('FECHA') . "</th>";
		$commentsHTML .= "</tr>";
	
		while($row = DB_fetch_array($rs)) {
				
			if($i % 2 == 0) {
				$commentsHTML .= '<tr class="EvenTableRows">';
			} else {
				$commentsHTML .= '<tr class="OddTableRows">';
			}
	
			$commentsHTML .= "<td>{$row['realname']}</td>";
			$commentsHTML .= "<td>{$row['comment']}</td>";
			$commentsHTML .= "<td>{$row['date']}</td>";
			$i++;
		}
		$commentsHTML .= "</table>";
	}
	
	return $commentsHTML;
}

?>