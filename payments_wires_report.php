<?php
session_start();
require_once('tcpdf_min/config/tcpdf_config.php');
require_once('tcpdf_min/tcpdf.php');
include 'include/header.php';
include 'functions/functions.php';
error_reporting(E_ALL);
ini_set('display_errors', '1');

$project_id = @$_GET['project_id'];
$abr = @$_GET['abr'];
$asbr = @$_GET['asbr'];
$acpr = @$_GET['acpr'];

$query = $mysqli->prepare("SELECT * FROM dne_projects WHERE id = ?");
$query->bind_param("i",$project_id);
$query->execute();
$query->store_result();
$project = fetch_unique($query);

$query = $mysqli->prepare("SELECT * FROM dne_vat");
$query->execute(); 
$query->store_result();
$vat = fetch_unique($query);

$_SESSION['lang'] = $project->lang;

$dir_table = 'ltr';
$style_table = "margin-top:25px;";
$style_td = "text-align:left;padding-left:12px;";
$style_td_totals = "text-align:righ;padding-right:12px;";
$name_sort = 's.name';
$title = @$project->name_he.'<br/>רשימת העברות תשלומים<br/>'.substr(date('Y-m-d'),8,2).'/'.substr(date('Y-m-d'),5,2).'/'.substr(date('Y-m-d'),0,4);

if($_SESSION['lang'] == 'HE') {
    $dir_table = 'rtl';
	$style_table = "margin-top:25px;margin-left:2.3%;";
	$style_td = "text-align:right;padding-right:12px;";
    $style_td_totals = "text-align:left;padding-left:12px;";
	$name_sort = 's.name_he';
}

$is_appears_pdf_wires = 1;

$query = $mysqli->prepare("SELECT p.id_projects_suppliers AS id_projects_suppliers,SUM(p.approved_amount) AS sum_approved_amount,SUM(p.paid_amount) AS sum_paid_amount,
                          SUM(p.approved_amount)-SUM(p.paid_amount) AS sum_to_pay_amount,s.name AS name,s.bank_account_owner AS bank_account_owner,s.bank_name AS bank_name,
						  s.bank_branche AS bank_branche,s.bank_account_number AS bank_account_number,s.swift AS swift,s.iban AS iban
						  FROM dne_payments p 
						  LEFT JOIN dne_projects_suppliers ps on p.id_projects_suppliers = ps.id
						  LEFT JOIN dne_suppliers s ON ps.id_supplier = s.id
						  WHERE ps.id_project = ? AND ps.is_appears_pdf_wires = ?
						  GROUP BY p.id_projects_suppliers ORDER BY s.type,".$name_sort);
$query->bind_param("ii",$project_id,$is_appears_pdf_wires);
$query->execute();
$query->store_result();
$payments = fetch($query);

$query = $mysqli->prepare("SELECT o.id_projects_suppliers AS id_projects_suppliers,SUM(o.sum_order) AS total_sum_order,o.vat AS vat
                          FROM dne_orders o 
						  LEFT JOIN dne_projects_suppliers ps on o.id_projects_suppliers = ps.id 
						  LEFT JOIN dne_suppliers s ON ps.id_supplier = s.id
						  WHERE ps.id_project = ?
						  GROUP BY o.id_projects_suppliers ORDER BY s.type,".$name_sort);
$query->bind_param("i",$project_id);
$query->execute();
$query->store_result();
$orders = fetch($query);

$total_sum_to_pay_amount = 0;
$total_sum_order = 0;
$total_sum_order_vat = 0;
$total_sum_order_vat_included = 0;
$total_sum_paid = 0;
$total_sum_paid_vat = 0;
$total_sum_paid_vat_included = 0;
$total_balance = 0;
$total_balance_vat = 0;
$total_balance_vat_included = 0;
$total_to_pay = 0;
$count1 = 0;	
$count2 = 0;	
$count4 = 0;

$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_STRING, array(0,64,255), array(0,64,128));
$pdf->setFooterData(array(0,64,0), array(0,64,128));
$pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
$pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
$pdf->setFontSubsetting(true);
$pdf->SetFont('freesans', '', 12);
$pdf->setPrintHeader(false);

$html_header = '<table width="90%"><tr><td style="text-align:center;"><img src="images/davidnahmias_stripe.jpg" /><br/><br/></td></tr>';
$html1_body = '<tr><td style="text-align:center;padding-top:30px;"><span dir="'.$dir_table.'"><strong><u>'.@$title.'</u></strong></span></td></tr></table>';
$html1_body.= '<div class="row">';
$html1_body.= '<div class="col-md-12">';
$html1_body.= '<table dir="'.$dir_table.'" style="'.$style_table.'"><tr><td width="2%">&nbsp;</td><td><table cellpadding="4">';
$html1_body.='<tr height="35px;" style="font-size:12px;font-weight:bold;background-color:silver;">';
$html1_body.='<th width="160px;" style="text-align:center;border:1px solid black;">Supplier Name</th>';
$html1_body.='<th width="130px" style="text-align:center;border:1px solid black;">Amount to pay/ <br/> סכום לתשלום</th>';
$html1_body.='<th width="80px;" style="text-align:center;border:1px solid black;">Swift</th>';
$html1_body.='<th width="80px;" style="text-align:center;border:1px solid black;">Iban</th>';
$html1_body.='<th width="80px;" style="text-align:center;border:1px solid black;">חשבון</th>';
$html1_body.='<th width="70px;" style="text-align:center;border:1px solid black;">Branch/ <br/> סניף</th>';
$html1_body.='<th width="70px;" style="text-align:center;border:1px solid black;">Bank/ <br/> בנק</th>';
$html1_body.='<th width="220px;" style="text-align:center;border:1px solid black;">שם חשבון</th>';
$html1_body.='</tr>';			
					
foreach($payments as $item) {	
    if($item->sum_approved_amount - $item->sum_paid_amount > 0) {				
		$count1++;
		
		$sum_to_pay_amount = '';
		if($item->sum_to_pay_amount > 0) {
			$sum_to_pay_amount = $item->sum_to_pay_amount;
			$total_sum_to_pay_amount +=$item->sum_to_pay_amount;
		}
		
		$html1_body.='<tr height="25px;" style="font-size:12px;">';	
		$html1_body.='<td style="'.$style_td.'border:1px solid black;">';
		if($_SESSION['lang'] != 'HE')
		    $html1_body.=@$item->name;
		else 
			$html1_body.=@$item->name_he;
		$html1_body.='</td>';
		$html1_body.='<td style="text-align:center;border:1px solid black;">'.number_format(@$sum_to_pay_amount,2,'.',',').'&#8362;</td>';
		$html1_body.='<td style="text-align:center;border:1px solid black;">'.@$item->swift.'</td>';
		$html1_body.='<td style="text-align:center;border:1px solid black;">'.@$item->iban.'</td>';
		$html1_body.='<td style="text-align:center;border:1px solid black;">'.@$item->bank_account_number.'</td>';
		$html1_body.='<td style="text-align:center;border:1px solid black;">'.@$item->bank_branche.'</td>';
		$html1_body.='<td style="text-align:center;border:1px solid black;">'.@$item->bank_name.'</td>';
		$html1_body.='<td style="text-align:right;padding-right:5px;border:1px solid black;">'.@$item->bank_account_owner.'</td>';
		$html1_body.='</tr>';
	}
}

$html1_body.='<tr style="height:25px;font-size:13px;">';
$html1_body.='<td style=";border:1px solid black;">&nbsp;</td>';
$html1_body.='<td style="text-align:center;background-color:#dcf1fa;border:1px solid black;"><strong><span style="font-size:11px;">'.getLang('total_sum_amount_to_pay').'</span><br/>'.number_format(@$total_sum_to_pay_amount,2,'.',',').'&#8362;</strong></td>';
$html1_body.='<td colspan="8" style=";border:1px solid black;">&nbsp;</td>';
$html1_body.='</tr>';
$html1_body.='</table></td></tr></table>';
$html1_body.='</div>';
$html1_body.='</div>';

$html1 = $html_header.$html1_body;

$pdf->AddPage();
$pdf->setTextShadow(array('enabled'=>true, 'depth_w'=>0.2, 'depth_h'=>0.2, 'color'=>array(196,196,196), 'opacity'=>1, 'blend_mode'=>'Normal'));
$pdf->writeHTMLCell(0, 0, '', '', $html1, 0, 1, 0, true, '', true);

$title_budget_report = $project->name_he.'<br/>דוח תקציב<br/>'.substr(date('Y-m-d'),8,2).'/'.substr(date('Y-m-d'),5,2).'/'.substr(date('Y-m-d'),0,4);

if($abr == 1) {
	$html2_body = '<tr><td width="12px;">&nbsp;</td><td style="text-align:center;padding-top:30px;"><span dir="'.$dir_table.'"><strong><u>'.$title_budget_report.'</u></strong></span></td></tr></table>';
	$html2_body.= '<div class="row">';
	$html2_body.= '<div class="col-md-12">';
	$html2_body.= '<table dir="'.$dir_table.'" style="'.$style_table.'"><tr><td width="4%">&nbsp;</td><td><table cellpadding="4">';
	$html2_body.='<tr height="35px;" style="font-size:12px;font-weight:bold;">';
	$html2_body.='<th colspan="3" style="text-align:center;background-color:silver;border:1px solid black;" width="350px;">'.getLang('supplier').'</th>';
	$html2_body.='<th colspan="3" width="330px;" style="text-align:center;border:1px solid black;">'.getLang('ht').'</th>';
	$html2_body.='<th width="20px" style="background-color:white;">&nbsp;</th>';
	$html2_body.='<th colspan="2" width="220px;" style="text-align:center;border:1px solid black;background-color:#dcf1fa;">'.getLang('ttc').'</th>';
	$html2_body.='</tr>';
	$html2_body.='<tr height="35px;" style="font-size:12px;font-weight:bold;">';
	$html2_body.='<th width="30px;" style="text-align:center;border:1px solid black;">#</th>';
	$html2_body.='<th width="160px;" style="text-align:center;border:1px solid black;">'.getLang('supplier_name').'</th>';
	$html2_body.='<th width="160px;" style="text-align:center;border:1px solid black;">'.getLang('supplier_domain').'</th>';
	$html2_body.='<th width="110px;" style="text-align:center;border:1px solid black;">'.getLang('total_orders').'</th>';
	$html2_body.='<th width="110px;" style="text-align:center;border:1px solid black;">'.getLang('paid_amount').'</th>';
	$html2_body.='<th width="110px;" style="text-align:center;border:1px solid black;">'.getLang('order_balance').'</th>';
	$html2_body.='<th width="20px" style="background-color:white;">&nbsp;</th>';
	$html2_body.='<th width="110px;" style="text-align:center;border:1px solid black;">'.getLang('order_balance').'</th>';
	$html2_body.='<th width="110px;" style="text-align:center;border:1px solid black;">'.getLang('account_to_be_paid').'</th>';
	$html2_body.='</tr>';

	foreach($orders as $item) { 
    $count2++;
	
    $data_bg_color = '#ffffff';
	if($count2%2 != 0) 
	   $data_bg_color = '#dedede';
   
    $query = $mysqli->prepare("SELECT SUM(p.paid_amount_vat_excluded) AS sum_paid_amount,SUM(p.approved_amount)-SUM(p.paid_amount) AS account_to_be_paid,
							  s.name AS name,s.name_he AS name_he,sfow.name AS sfow_name,sfow.name_he AS sfow_name_he
							  FROM dne_payments p
							  LEFT JOIN dne_projects_suppliers ps on p.id_projects_suppliers = ps.id 
							  LEFT JOIN dne_suppliers s ON ps.id_supplier = s.id
							  LEFT JOIN dne_sup_field_of_work sfow ON s.id_field_of_work = sfow.id
							  WHERE ps.id = ? ORDER BY s.type,".$name_sort);
    $query->bind_param("i",$item->id_projects_suppliers);
    $query->execute();
    $query->store_result();
    $payment = fetch_unique($query);
	
    $total_sum_order_unique = round($item->total_sum_order);

	$sum_paid_amount = 0;
	if($payment->sum_paid_amount > 0) 
		$sum_paid_amount = round($payment->sum_paid_amount);
	
	$balance = round($item->total_sum_order-$payment->sum_paid_amount);
	$balance_vat_included = round($balance*(1+($item->vat/100)));
	
	$account_to_be_paid = 0;
	$account_to_be_paid_display = '';
	if($payment->account_to_be_paid > 0) { 
		$account_to_be_paid = round($payment->account_to_be_paid);
		$total_to_pay += $account_to_be_paid;
		$account_to_be_paid_display = number_format($account_to_be_paid,0,'.', ',').'&nbsp;&#x20aa;';
	}
	else $account_to_be_paid = '';
	
	$total_sum_order += $item->total_sum_order;
	$total_sum_order_vat += $item->total_sum_order*($item->vat/100);
	$total_sum_order_vat_included += $item->total_sum_order*(1+($item->vat/100));	
	
	$total_sum_paid += $payment->sum_paid_amount;
	$total_sum_paid_vat += $payment->sum_paid_amount*($item->vat/100);
	$total_sum_paid_vat_included += $payment->sum_paid_amount*(1+($item->vat/100));
	
	$total_balance += $balance;
	$total_balance_vat += $balance*($item->vat/100);
	$total_balance_vat_included += $balance*(1+($item->vat/100));
	
    $html2_body.='<tr height="30px;" style="font-size:12px;background-color:'.$data_bg_color.';">'; 
	$html2_body.='<td width="30px;" style="text-align:center;border:1px solid black;">'.$count2.'</td>';
	$html2_body.='<td width="160px;" style="'.$style_td.'border:1px solid black;">&nbsp;';
	if($_SESSION['lang'] != 'HE') 
	    $html2_body.=@$payment->name;
	else 
		$html2_body.=@$payment->name_he;
	$html2_body.='</td>';
	$html2_body.='<td width="160px;" style="'.$style_td.'border:1px solid black;">';
	if($_SESSION['lang'] != 'HE') 
	    $html2_body.=@$payment->sfow_name;
	else 
		$html2_body.=@$payment->sfow_name_he;
	$html2_body.='</td>';
	$html2_body.='<td width="110px;" style="text-align:right;padding-right:12px;border:1px solid black;">'.number_format($total_sum_order_unique,0,'.',',').'&nbsp;&#x20aa;</td>';
	$html2_body.='<td width="110px;" style="text-align:right;padding-right:12px;border:1px solid black;">'.number_format($sum_paid_amount,0,'.', ',').'&nbsp;&#x20aa;</td>';
	$html2_body.='<td width="110px;" style="text-align:right;padding-right:12px;border:1px solid black;">'.number_format($balance,0,'.', ',').'&nbsp;&#x20aa;</td>';
	$html2_body.='<td style="background-color:white;">&nbsp;</td>';
	$html2_body.='<td width="110px;" style="text-align:right;padding-right:12px;border:1px solid black;">'.number_format($balance_vat_included,0,'.',',').'&nbsp;&#x20aa;</td>';
	$html2_body.='<td width="110px;" style="text-align:right;padding-right:12px;border:1px solid black;">'.$account_to_be_paid_display.'</td>';
    $html2_body.='</tr>';
}

$total_to_pay_display = '';
if($total_to_pay != 0)
	$total_to_pay_display = number_format($total_to_pay,0,'.',',').'&nbsp;&#x20aa;';

$html2_body.='<tr>';
$html2_body.='<td colspan="9">&nbsp;</td>';
$html2_body.='</tr>';

$html2_body.='<tr><td colspan="9">&nbsp;</td></tr>';
$html2_body.='<tr height="35px;" style="font-size:12px;font-weight:bold;background-color:silver;">';
$html2_body.='<td colspan="3" style="'.$style_td_totals.'border:1px solid black;">'.getLang('total_ht').'</td>';
$html2_body.='<td style="text-align:right;padding-right:12px;border:1px solid black;">'.number_format($total_sum_order,0,'.',',').'&nbsp;&#x20aa;</td>';
$html2_body.='<td style="text-align:right;padding-right:12px;border:1px solid black;">'.number_format($total_sum_paid,0,'.',',').'&nbsp;&#x20aa;</td>';
$html2_body.='<td style="text-align:right;padding-right:12px;border:1px solid black;">'.number_format($total_balance,0,'.',',').'&nbsp;&#x20aa;</td>';
$html2_body.='<td style="background-color:white;">&nbsp;</td>';
$html2_body.='<td style="text-align:right;padding-right:12px;background-color:#dcf1fa;border:1px solid black;">'.getLang('total_balance').'</td>';
$html2_body.='<td style="text-align:right;padding-right:12px;background-color:#dcf1fa;border:1px solid black;">'.getLang('total_to_be_paid').'</td>';
$html2_body.='</tr>';

$html2_body.='<tr height="24px;" style="font-size:12px;font-weight:bold;background-color:#dcf1fa;">';
$html2_body.='<td colspan="3" style="'.$style_td_totals.'border:1px solid black;">'.getLang('total_ttc').'</td>';
$html2_body.='<td style="text-align:right;padding-right:12px;border:1px solid black;">'.number_format($total_sum_order_vat_included,0,'.',',').'&nbsp;&#x20aa;</td>';
$html2_body.='<td style="text-align:right;padding-right:12px;border:1px solid black;">'.number_format($total_sum_paid_vat_included,0,'.',',').'&nbsp;&#x20aa;</td>';
$html2_body.='<td style="text-align:right;padding-right:12px;border:1px solid black;">'.number_format($total_balance_vat_included,0,'.',',').'&nbsp;&#x20aa;</td>';
$html2_body.='<td style="background-color:white;">&nbsp;</td>';
$html2_body.='<td style="text-align:right;padding-right:12px;background-color:#dcf1fa;border:1px solid black;">'.number_format($total_balance_vat_included,0,'.',',').'&nbsp;&#x20aa;</td>';
$html2_body.='<td style="text-align:right;padding-right:12px;background-color:#dcf1fa;border:1px solid black;">'.$total_to_pay_display.'</td>';
$html2_body.='</tr>';
$html2_body.='</table></td></tr></table>';
$html2_body.='</div>';
$html2_body.='</div>';

$html2 = $html_header.$html2_body;

if($_SESSION['lang'] == 'HE')
	$pdf->setRTL(true);
		
	$pdf->AddPage();
	$pdf->setTextShadow(array('enabled'=>true, 'depth_w'=>0.2, 'depth_h'=>0.2, 'color'=>array(196,196,196), 'opacity'=>1, 'blend_mode'=>'Normal'));
	$pdf->writeHTMLCell(0, 0, '', '', $html2, 0, 1, 0, true, '', true);
}

if($asbr == 1) {
	foreach($payments as $item) {
		$query = $mysqli->prepare("SELECT s.name AS name,s.name_he AS name_he,sfow.name AS sfow_name,sfow.name_he AS sfow_name_he
								  FROM dne_projects_suppliers ps
								  LEFT JOIN dne_suppliers s ON ps.id_supplier = s.id
								  LEFT JOIN dne_sup_field_of_work sfow ON s.id_field_of_work = sfow.id
								  WHERE ps.id = ? ORDER BY s.type,".$name_sort);
		$query->bind_param("i",$item->id_projects_suppliers);
		$query->execute(); 
		$query->store_result();
		$supplier = fetch_unique($query);
		
		$title_orders_payments = $supplier->name_he.'<br/>'.$supplier->sfow_name_he.'<br/>דוח הזמנות/תשלומים<br/>'.substr(date('Y-m-d'),8,2).'/'.substr(date('Y-m-d'),5,2).'/'.substr(date('Y-m-d'),0,4);

		$html3_body = '';
		$count3 = 0;

		$query = $mysqli->prepare("SELECT o.signature_date AS signature_date,o.description AS description,o.sum_order AS sum_order,o.vat AS vat
								  FROM dne_orders o 
								  LEFT JOIN dne_projects_suppliers ps ON o.id_projects_suppliers = ps.id 
								  WHERE ps.id = ? ORDER BY o.signature_date");
		$query->bind_param("i",$item->id_projects_suppliers);
		$query->execute(); 
		$query->store_result();
		$elems_orders = fetch($query);

		$query = $mysqli->prepare("SELECT p.payment_date AS payment_date,p.description AS description,p.paid_amount_vat_excluded AS paid_amount_vat_excluded,
								  p.paid_amount AS paid_amount_vat_included,p.vat AS vat
								  FROM dne_payments p
								  LEFT JOIN dne_projects_suppliers ps ON p.id_projects_suppliers = ps.id 
								  WHERE ps.id = ? ORDER BY p.payment_date");
		$query->bind_param("i",$item->id_projects_suppliers);
		$query->execute(); 
		$query->store_result();
		$payments_num_rows = $query->num_rows;
		$elems_payments = fetch($query);
		
		$isPartialPayment = 0;		  
		
        $query = $mysqli->prepare("SELECT p.id AS id,p.id_projects_suppliers AS id_projects_suppliers,p.description AS description,
						  p.submit_date AS submit_date,p.pdf_submission AS pdf_submission,p.submitted_account AS submitted_account,p.approval_date AS approval_date,
						  p.pdf_approval AS pdf_approval,p.approved_amount AS approved_amount,p.payment_date As payment_date,p.pdf_payment AS pdf_payment,
					      p.paid_amount AS paid_amount_vat_included,p.vat AS vat,p.paid_amount_vat_excluded AS paid_amount_vat_excluded,
						  p.invoice_date AS invoice_date,p.pdf_invoice AS pdf_invoice,
						  ps.is_appears_pdf_wires AS is_appears_pdf_wires,s.nickname AS nickname				  
						  FROM dne_payments p
						  LEFT JOIN dne_projects_suppliers ps on p.id_projects_suppliers = ps.id
						  LEFT JOIN dne_suppliers s ON ps.id_supplier = s.id
						  WHERE p.IsTotalPayment = ? AND p.id_projects_suppliers = ?
						  ORDER BY p.approval_date DESC");
		$query->bind_param("ii",$isPartialPayment,$item->id_projects_suppliers);
		$query->execute(); 
		$query->store_result();
		$elem_partial_payments_num_rows = $query->num_rows;
		$elem_partial_payments = fetch($query);

		
		$html3_body.= '<tr><td style="text-align:center;padding-top:30px;"><span dir="'.$dir_table.'"><strong><u>';
		$html3_body.= $title_orders_payments.'</u></strong></span></td></tr></table>';
		$html3_body.= '<div class="row">';
		$html3_body.= '<div class="col-md-12">';
		$html3_body.= '<table dir="'.$dir_table.'" style="'.$style_table.'"><tr><td width="4%">&nbsp;</td><td><table cellpadding="4">';
		$html3_body.='<tr height="35px;" style="font-size:12px;font-weight:bold;background-color:silver;">';
		$html3_body.='<th width="70px;" style="text-align:center;border:1px solid black;">'.getLang('date').'</th>';
		$html3_body.='<th width="70px;" style="text-align:center;border:1px solid black;">'.getLang('order_payment').'</th>';
		$html3_body.='<th width="320;" style="text-align:center;border:1px solid black;">'.getLang('description').'</th>';
		$html3_body.='<th width="100px;" style="text-align:center;border:1px solid black;">'.getLang('order_excluding_vat').'</th>';
		$html3_body.='<th width="100px;" style="text-align:center;border:1px solid black;">'.getLang('payment_excluding_vat').'</th>';
		$html3_body.='<th width="20px;" style="background-color:white;">&nbsp;</th>';
		$html3_body.='<th width="100px;" style="text-align:center;border:1px solid black;">'.getLang('payment_including_vat').'</th>';
		$html3_body.='</tr>';

		$elem_total_sum_order = 0;
		$elem_total_sum_order_vat_included = 0;
		
		foreach($elems_orders as $item) { 
			$count3++;
			$data_bg_color = '#ffffff';
			
			if($count3%2 != 0) 
			   $data_bg_color = '#dedede';

			$signature_date = '';
			if(@$item->signature_date != '0000-00-00')
				$signature_date = substr(@$item->signature_date,8,2).'/'.substr(@$item->signature_date,5,2).'/'.substr(@$item->signature_date,2,2);
			
			$sum_order = '';
			if(@$item->sum_order != 0.00)
				$sum_order = number_format(@$item->sum_order,0,'.',',');
			
			$elem_total_sum_order += $item->sum_order;
			
		    $html3_body.='<tr height="25px;" style="font-size:12px;background-color:'.$data_bg_color.'">';	
		    $html3_body.='<td style="text-align:left;padding-left:12px;border:1px solid black;">&nbsp;'.@$signature_date.'</td>';
		    $html3_body.='<td style="text-align:center;border:1px solid black;">&nbsp;'.getLang('order').'</td>';
		    $html3_body.='<td style="'.$style_td.'border:1px solid black;">&nbsp;'.@$item->description.'</td>';
		    $html3_body.='<td style="text-align:right;padding-right:12px;border:1px solid black;">'.@number_format($item->sum_order,0,'.',',').'&nbsp;&#x20aa;&nbsp;</td>';
		    $html3_body.='<td style="text-align:right;padding-right:5px;border:1px solid black;">&nbsp;</td>';
		    $html3_body.='<td style="background-color:white;">&nbsp;</td>';
		    $html3_body.='<td style="text-align:right;padding-right:5px;border:1px solid black;">&nbsp;</td>';
		    $html3_body.='</tr>';
		   
		    $elem_total_sum_order_vat_included = $elem_total_sum_order*(1+($item->vat/100));
		}

		$elem_total_paid_amount_vat_excluded = 0;
		$elem_total_paid_amount_vat_included = 0;
		
		foreach($elems_payments as $item) { 
			$count3++;
			$data_bg_color = '#ffffff';
			if($count3%2 != 0) 
			   $data_bg_color = '#dedede';
		   
			$payment_date = '';
			if(@$item->payment_date != '0000-00-00')
				$payment_date = substr(@$item->payment_date,8,2).'/'.substr(@$item->payment_date,5,2).'/'.substr(@$item->payment_date,2,2);
			
			$paid_amount_vat_excluded_display = '';
			if(@$item->paid_amount_vat_excluded != 0.00) {
				$paid_amount_vat_excluded = @$item->paid_amount_vat_excluded;
				$paid_amount_vat_excluded_display = '-'.number_format(@$paid_amount_vat_excluded,0,'.',',').'&#8362;';
			}
	
			$paid_amount_vat_included_display = '';
			if(@$item->paid_amount_vat_included != 0.00) {
				$paid_amount_vat_included = @$item->paid_amount_vat_included;
				$paid_amount_vat_included_display = '-'.number_format(@$paid_amount_vat_included,0,'.',',').'&#8362;';
			}

			$elem_total_paid_amount_vat_excluded +=$item->paid_amount_vat_excluded;
			$elem_total_paid_amount_vat_included +=$item->paid_amount_vat_included;
			$elem_balance_vat_excluded = $elem_total_sum_order-$elem_total_paid_amount_vat_excluded;
			$elem_balance_vat_included = $elem_total_sum_order_vat_included-$elem_total_paid_amount_vat_included;
				
			$html3_body.='<tr height="25px;" style="font-size:12px;background-color:'.$data_bg_color.'">';	
			$html3_body.='<td style="text-align:left;padding-left:12px;border:1px solid black;">&nbsp;'.@$payment_date.'</td>';
			$html3_body.='<td style="text-align:center;border:1px solid black;">&nbsp;'.getLang('payment').'</td>';
			$html3_body.='<td style="'.$style_td.'border:1px solid black;">&nbsp;'.@$item->description.'</td>';
			$html3_body.='<td style="text-align:right;padding-right:5px;border:1px solid black;">&nbsp;</td>';
			$html3_body.='<td style="text-align:right;padding-right:5px;border:1px solid black;">'.@$paid_amount_vat_excluded_display.'</td>';
			$html3_body.='<td style="background-color:white;">&nbsp;</td>';
			$html3_body.='<td style="text-align:right;padding-right:5px;border:1px solid black;">'.@$paid_amount_vat_included_display.'</td>';
			$html3_body.='</tr>';
		}
	   
		$elem_total_sum_order = number_format(@$elem_total_sum_order,2,'.',',');

		if($payments_num_rows > 0) {
			$elem_total_paid_amount_vat_excluded = '-'.number_format(@$elem_total_paid_amount_vat_excluded,2,'.',',').'&#8362;';
			$elem_total_paid_amount_vat_included = '-'.number_format(@$elem_total_paid_amount_vat_included,2,'.',',').'&#8362;';
			$elem_balance_vat_excluded = number_format(@$elem_balance_vat_excluded,2,'.',',').'&#8362;';
			$elem_balance_vat_included = number_format(@$elem_balance_vat_included,2,'.',',').'&#8362;';
		}
		else {
			$elem_total_paid_amount_vat_excluded = '';
			$elem_total_paid_amount_vat_included = '';
			$elem_balance_vat_excluded = $elem_total_sum_order.'&#8362;';
			$elem_balance_vat_included = number_format($elem_total_sum_order_vat_included,2,'.',',').'&#8362;';
		}				
	
		$html3_body.='<tr style="background-color:silver;font-size:12px;">';
		$html3_body.='<td colspan="3" style="'.$style_td_totals.'border:1px solid black;"><strong>'.getLang('total').'</strong></td>';
		$html3_body.='<td style="text-align:right;padding-right:5px;border:1px solid black;"><strong>'.@$elem_total_sum_order.'&nbsp;&#8362;</strong></td>';
		$html3_body.='<td style="text-align:right;padding-right:5px;border:1px solid black;"><strong>'.@$elem_total_paid_amount_vat_excluded.'</strong></td>';
		$html3_body.='<td style="background-color:white;">&nbsp;</td>';
		$html3_body.='<td style="text-align:right;padding-right:5px;border:1px solid black;"><strong>'.@$elem_total_paid_amount_vat_included.'</strong></td>';
		$html3_body.='</tr>';

		$html3_body.='<tr style="height:30px;background-color:#dcf1fa;font-size:12px;">';
		$html3_body.='<td colspan="4" style="'.$style_td_totals.'border:1px solid black;"><strong>'.getLang('order_balance').'</strong></td>';
		$html3_body.='<td style="text-align:right;padding-right:5px;border:1px solid black;"><strong>'.@$elem_balance_vat_excluded.'</strong></td>';
		$html3_body.='<td style="background-color:white;">&nbsp;</td>';
		$html3_body.='<td style="text-align:right;padding-right:5px;border:1px solid black;"><strong>'.@$elem_balance_vat_included.'</strong></td>';
		$html3_body.='</tr>';
		$html3_body.='</table></td></tr></table>';
		
		if($elem_partial_payments_num_rows > 0) {
			$html3_body.='<table dir="'.$dir_table.'" style="'.$style_table.'"><tr><td>&nbsp;</td></tr><tr><td width="40%">&nbsp;</td><td style="font-size:16px;"><strong>תשלומים נוכחים</strong></td></tr></table>';
			$html3_body.='<table dir="'.$dir_table.'" style="'.$style_table.'"><tr><td>&nbsp;</td></tr><tr><td width="4%">&nbsp;</td><td><table>';	
			$html3_body.='<tr height="35px;" style="font-size:12px;font-weight:bold;background-color:silver;">';
			$html3_body.='<th width="30px;" style="text-align:center;border:1px solid black!important;">&#x2116;</th>';
			$html3_body.='<th width="80px;" style="text-align:center;border:1px solid black!important;">&nbsp;'.getLang('submit_date').'</th>';
			$html3_body.='<th width="120px;" style="text-align:center;border:1px solid black!important;">&nbsp;'.getLang('description').'</th>';
			$html3_body.='<th width="120px;" style="text-align:center;border:1px solid black!important;">&nbsp;'.getLang('submitted_account').'</th>';
			$html3_body.='<th width="80px;;" style="text-align:center;border:1px solid black!important;">&nbsp;'.getLang('approval_date').'</th>';
			$html3_body.='<th width="100px;;" style="text-align:center;border:1px solid black!important;">&nbsp;'.getLang('approved_amount').'</th>';
			$html3_body.='<th width="80px;" style="text-align:center;border:1px solid black!important;">&nbsp;'.getLang('payment_date').'</th>';
			$html3_body.='<th width="100px;" style="text-align:center;border:1px solid black!important;">&nbsp;'.getLang('paid_amount').'</th>';	
			$html3_body.='<th width="50px;" style="text-align:center;border:1px solid black!important;">&nbsp;'.getLang('vat').'</th>';
			$html3_body.='<th width="100px;" style="text-align:center;border:1px solid black!important;">&nbsp;'.getLang('order_balance').'</th>';
			$html3_body.='<th width="80px;" style="text-align:center;border:1px solid black!important;">&nbsp;'.getLang('invoice_date').'</th>';													
			$html3_body.='</tr>';
										
			$count = 0;
			foreach($elem_partial_payments as $item) {
				$count++;
				
				$submit_date = '';
				if(@$item->submit_date != '0000-00-00')
					$submit_date = substr(@$item->submit_date,8,2).'/'.substr(@$item->submit_date,5,2).'/'.substr(@$item->submit_date,2,2);
				
				$submitted_account = '';
				if(@$item->submitted_account != 0.00)
				   $submitted_account = number_format(@$item->submitted_account,2,'.',',').'&nbsp;&#8362;';
				
				$approval_date = '';
				if(@$item->approval_date != '0000-00-00')
					$approval_date = substr(@$item->approval_date,8,2).'/'.substr(@$item->approval_date,5,2).'/'.substr(@$item->approval_date,2,2);
				
				$approved_amount = '';
				if(@$item->approved_amount != 0.00)
				   $approved_amount = number_format(@$item->approved_amount,2,'.',',').'&nbsp;&#8362;';
			   
				$payment_date = '';
				if(@$item->payment_date != '0000-00-00')
					$payment_date = substr(@$item->payment_date,8,2).'/'.substr(@$item->payment_date,5,2).'/'.substr(@$item->payment_date,2,2);
				
				$paid_amount_vat_included = '';
				if(@$item->paid_amount_vat_included != 0.00)
					$paid_amount_vat_included = number_format(@$item->paid_amount_vat_included,2,'.',',').'&nbsp;&#8362;';
				
				$paid_amount_vat_excluded = '';
				if(@$item->paid_amount_vat_excluded != 0.00)
					$paid_amount_vat_excluded = number_format(@$item->paid_amount_vat_excluded,2,'.',',').'&nbsp;&#8362;';
				
				$balance = number_format(@$item->approved_amount - @$item->paid_amount_vat_included,2,'.',',').'&nbsp;&#8362;';
				
				$invoice_date = '';
				if(@$item->invoice_date != '0000-00-00')
					$invoice_date = substr(@$item->invoice_date,8,2).'/'.substr(@$item->invoice_date,5,2).'/'.substr(@$item->invoice_date,2,2);
				
				$html3_body.='<tr style="height:30px;font-size:12px;">';
				$html3_body.='<td style="text-align:center;border:1px solid black!important;">'.@$count.'</td>';		
				$html3_body.='<td style="text-align:left;padding-left:5px;border:1px solid black!important;">&nbsp;'.$submit_date.'</td>';									
				$html3_body.='<td style="text-align:left;padding-left:5px;border:1px solid black!important;">&nbsp;'.@$item->description.'</td>';
				$html3_body.='<td style="text-align:right;padding-right:5px;border:1px solid black!important;">&nbsp;'.@$submitted_account.'</td>';
				$html3_body.='<td style="text-align:left;padding-left:5px;border:1px solid black!important;">&nbsp;'.$approval_date.'</td>'; 
				$html3_body.='<td style="text-align:right;padding-right:5px;border:1px solid black!important;">&nbsp;'.@$approved_amount.'</td>';
				$html3_body.='<td style="text-align:left;padding-left:5px;border:1px solid black!important;">&nbsp;'.$payment_date.'</td>';
				$html3_body.='<td style="text-align:right;padding-right:5px;border:1px solid black!important;">&nbsp;'.$paid_amount_vat_included.'</td>';
				$html3_body.='<td style="text-align:center;border:1px solid black!important;">&nbsp;'.number_format(@$item->vat,0,'.',',').'%</td>';
				$html3_body.='<td style="text-align:center;border:1px solid black!important;">&nbsp;'.@$balance.'</td>';
				$html3_body.='<td style="text-align:center;border:1px solid black!important;">&nbsp;'.@$invoice_date.'</td>';
				$html3_body.='</tr>';
	    }
	$html3_body.='</table>';
	$html3_body.='</td></tr></table>';
}
		
		$html3_body.='</div>';
		$html3_body.='</div>';
		
		$html3 = $html_header.$html3_body;
		
		if($_SESSION['lang'] == 'HE')
		   $pdf->setRTL(true);

		$pdf->AddPage();
		$pdf->setTextShadow(array('enabled'=>true, 'depth_w'=>0.2, 'depth_h'=>0.2, 'color'=>array(196,196,196), 'opacity'=>1, 'blend_mode'=>'Normal'));
		$pdf->writeHTMLCell(0, 0, '', '', $html3, 0, 1, 0, true, '', true);
	}
}

ob_end_clean();
$pdf_name = substr(date('Y-m-d'),0,4).substr(date('Y-m-d'),5,2).substr(date('Y-m-d'),8,2).'-Payments_Wires_List-'.$project->nickname.'.pdf';
$pdf->Output($pdf_name,'I');
?>