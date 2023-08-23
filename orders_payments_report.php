<?php
require_once('tcpdf_min/config/tcpdf_config.php');
require_once('tcpdf_min/tcpdf.php');
include 'include/header.php';
include 'functions/functions.php';
error_reporting(E_ALL);
ini_set('display_errors', '1');

$ps_id = @$_GET['ps_id'];
$project_id = @$_GET['project_id'];

$query = $mysqli->prepare("SELECT * FROM dne_projects WHERE id = ?");
$query->bind_param("i",$project_id);
$query->execute();
$query->store_result();
$project = fetch_unique($query);

$query = $mysqli->prepare("SELECT s.name AS name,s.name_he AS name_he,sfow.name AS sfow_name,sfow.name_he AS sfow_name_he
                          FROM dne_projects_suppliers ps
						  LEFT JOIN dne_projects p ON ps.id_project = p.id
						  LEFT JOIN dne_suppliers s ON ps.id_supplier = s.id
						  LEFT JOIN dne_sup_field_of_work sfow ON s.id_field_of_work = sfow.id
						  WHERE ps.id = ?");
$query->bind_param("i",$ps_id);
$query->execute(); 
$query->store_result();
$supplier = fetch_unique($query);

$_SESSION['lang'] = $project->lang;

$dir_table = 'ltr';
$style_table = "margin-top:30px;";
$style_td_totals = "text-align:right;padding-right:12px;";
$title = $supplier->name_he.'<br/>'.@$supplier->sfow_name_he.'<br/>דוח הזמנות/תשלומים<br/>'.substr(date('Y-m-d'),8,2).'/'.substr(date('Y-m-d'),5,2).'/'.substr(date('Y-m-d'),0,4);

if($_SESSION['lang'] == 'HE') {
    $dir_table = 'rtl';
	$style_table = "margin-top:30px;margin-left:2.3%;";
	$style_td_totals = "text-align:left;padding-left:12px;";
}

$query = $mysqli->prepare("SELECT o.signature_date AS signature_date,o.description AS description,o.sum_order AS sum_order,o.vat AS vat
						  FROM dne_orders o 
						  LEFT JOIN dne_projects_suppliers ps ON o.id_projects_suppliers = ps.id 
						  WHERE ps.id = ? ORDER BY o.signature_date");
$query->bind_param("i",$ps_id);
$query->execute(); 
$query->store_result();
$orders = fetch($query);

$query = $mysqli->prepare("SELECT p.payment_date AS payment_date,p.description AS description,p.paid_amount_vat_excluded AS paid_amount_vat_excluded,
                          p.paid_amount AS paid_amount_vat_included,p.vat AS vat
						  FROM dne_payments p
						  LEFT JOIN dne_projects_suppliers ps ON p.id_projects_suppliers = ps.id 
						  WHERE ps.id = ? ORDER BY p.payment_date");
$query->bind_param("i",$ps_id);
$query->execute(); 
$query->store_result();
$payments_num_rows = $query->num_rows;
$payments = fetch($query);

$isPartialPayment = 0;		  
$query = $mysqli->prepare("SELECT p.id AS id,p.id_projects_suppliers AS id_projects_suppliers,p.description AS description,
						  p.submit_date AS submit_date,p.pdf_submission AS pdf_submission,p.submitted_account AS submitted_account,p.approval_date AS approval_date,
						  p.pdf_approval AS pdf_approval,p.approved_amount AS approved_amount,p.payment_date As payment_date,p.pdf_payment AS pdf_payment,
					      p.paid_amount AS paid_amount_vat_included,p.vat AS vat,p.paid_amount_vat_excluded AS paid_amount_vat_excluded,
						  p.invoice_date AS invoice_date,p.pdf_invoice AS pdf_invoice,s.nickname AS nickname				  
						  FROM dne_payments p
						  LEFT JOIN dne_projects_suppliers ps on p.id_projects_suppliers = ps.id
						  LEFT JOIN dne_suppliers s ON ps.id_supplier = s.id
						  WHERE p.IsTotalPayment = ? AND p.id_projects_suppliers = ? ORDER BY p.approval_date DESC");
$query->bind_param("ii",$isPartialPayment,$ps_id);
$query->execute(); 
$query->store_result();
$partial_payments_num_rows = $query->num_rows;
$partial_payments = fetch($query);

$html = '<table width="90%" style="margin-top:15px;"><tr><td style="text-align:center;"><img src="images/davidnahmias_stripe.jpg" /><br/><br/></td></tr>';
$html.= '<tr><td style="text-align:center;padding-top:30px;"><span dir="'.$dir_table.'"><strong><u>'.$title.'</u></strong></span></td></tr></table>';
$html.= '<div class="row">';
$html.= '<div class="col-md-12">';
$html.= '<table dir="'.$dir_table.'" style="'.$style_table.'"><tr><td width="4%">&nbsp;</td><td><table cellpadding="4">';
$html.='<tr height="35px;" style="font-size:12px;font-weight:bold;background-color:silver;">';
$html.='<th width="70px;" style="text-align:center;border:1px solid black;">'.getLang('date').'</th>';
$html.='<th width="60px;" style="text-align:center;border:1px solid black;">'.getLang('order_payment').'</th>';
$html.='<th width="320;" style="text-align:center;border:1px solid black;">'.getLang('description').'</th>';
$html.='<th width="100px;" style="text-align:center;border:1px solid black;">'.getLang('order_excluding_vat').'</th>';
$html.='<th width="100px;" style="text-align:center;border:1px solid black;">'.getLang('payment_excluding_vat').'</th>';
$html.='<th width="20px;" style="background-color:white;">&nbsp;</th>';
$html.='<th width="100px;" style="text-align:center;background-color:#dcf1fa;border:1px solid black;">'.getLang('payment_including_vat').'</th>';
$html.='</tr>';

$count = 0;
$total_sum_order = 0;
$total_sum_order_vat_included = 0;

foreach($orders as $item) { 
    $count++;
    $data_bg_color = '#ffffff';
	
	if($count%2 != 0) 
	   $data_bg_color = '#dedede';

	$signature_date = '';
	if(@$item->signature_date != '0000-00-00')
		$signature_date = substr(@$item->signature_date,8,2).'/'.substr(@$item->signature_date,5,2).'/'.substr(@$item->signature_date,2,2);
	
	$sum_order = '';
	if(@$item->sum_order != 0.00)
		$sum_order = number_format(@$item->sum_order,0,'.',',');
	
	$total_sum_order += $item->sum_order;
	
    $html.='<tr height="25px;" style="font-size:12px;background-color:'.$data_bg_color.'">';	
	$html.='<td style="text-align:left;padding-left:12px;border:1px solid black;">&nbsp;'.@$signature_date.'</td>';
	$html.='<td style="text-align:center;border:1px solid black;">&nbsp;'.getLang('order').'</td>';
	$html.='<td style="text-align:left;padding-left:12px;border:1px solid black;">&nbsp;'.@$item->description.'</td>';
	$html.='<td style="text-align:right;padding-right:12px;border:1px solid black;">'.@number_format($item->sum_order,0,'.',',').'&nbsp;&#x20aa;&nbsp;</td>';
	$html.='<td style="text-align:right;padding-right:5px;border:1px solid black;">&nbsp;</td>';
	$html.='<td style="background-color:white;">&nbsp;</td>';
	$html.='<td style="text-align:right;padding-right:5px;border:1px solid black;">&nbsp;</td>';
	$html.='</tr>';
}

$total_sum_order_vat_included = $total_sum_order*(1+($item->vat/100));

$total_paid_amount_vat_excluded = 0;
$total_paid_amount_vat_included = 0;

foreach($payments as $item) { 
    $count++;
    $data_bg_color = '#ffffff';
	if($count%2 != 0) 
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

	$total_paid_amount_vat_excluded +=$item->paid_amount_vat_excluded;
	$total_paid_amount_vat_included +=$item->paid_amount_vat_included;
	$balance_vat_excluded = $total_sum_order-$total_paid_amount_vat_excluded;
	$balance_vat_included = $total_sum_order_vat_included-$total_paid_amount_vat_included;
		
    $html.='<tr height="25px;" style="font-size:12px;background-color:'.$data_bg_color.'">';	
	$html.='<td style="text-align:left;padding-left:12px;border:1px solid black;">&nbsp;'.@$payment_date.'</td>';
	$html.='<td style="text-align:center;border:1px solid black;">&nbsp;'.getLang('payment').'</td>';
	$html.='<td style="text-align:left;padding-left:12px;border:1px solid black;">&nbsp;'.@$item->description.'</td>';
	$html.='<td style="text-align:right;padding-right:5px;border:1px solid black;">&nbsp;</td>';
	$html.='<td style="text-align:right;padding-right:5px;border:1px solid black;">'.@$paid_amount_vat_excluded_display.'</td>';
	$html.='<td style="background-color:white;">&nbsp;</td>';
	$html.='<td style="text-align:right;padding-right:5px;border:1px solid black;">'.@$paid_amount_vat_included_display.'</td>';
	$html.='</tr>';
}

$total_sum_order = number_format(@$total_sum_order,2,'.',',');

if($payments_num_rows > 0) {
	$total_paid_amount_vat_excluded = '-'.number_format(@$total_paid_amount_vat_excluded,2,'.',',').'&#8362;';
	$total_paid_amount_vat_included = '-'.number_format(@$total_paid_amount_vat_included,2,'.',',').'&#8362;';
	$balance_vat_excluded = number_format(@$balance_vat_excluded,2,'.',',').'&#8362;';
	$balance_vat_included = number_format(@$balance_vat_included,2,'.',',').'&#8362;';
}
else {
	$total_paid_amount_vat_excluded = '';
	$total_paid_amount_vat_included = '';
	$balance_vat_excluded = $total_sum_order.'&#8362;';
	$balance_vat_included = number_format($total_sum_order_vat_included,2,'.',',').'&#8362;';
}				
	
$html.='<tr style="background-color:silver;font-size:12px;">';
$html.='<td colspan="3" style="'.$style_td_totals.'border:1px solid black;"><strong>'.getLang('total').'</strong></td>';
$html.='<td style="text-align:right;padding-right:5px;border:1px solid black;"><strong>'.@$total_sum_order.'&nbsp;&#8362;</strong></td>';
$html.='<td style="text-align:right;padding-right:5px;border:1px solid black;"><strong>'.@$total_paid_amount_vat_excluded.'</strong></td>';
$html.='<td style="background-color:white;">&nbsp;</td>';
$html.='<td style="text-align:right;padding-right:5px;border:1px solid black;"><strong>'.@$total_paid_amount_vat_included.'</strong></td>';
$html.='</tr>';

$html.='<tr style="height:30px;background-color:#dcf1fa;font-size:12px;">';
$html.='<td colspan="4" style="'.$style_td_totals.'border:1px solid black;"><strong>'.getLang('order_balance').'</strong></td>';
$html.='<td style="text-align:right;padding-right:5px;border:1px solid black;"><strong>'.@$balance_vat_excluded.'</strong></td>';
$html.='<td style="background-color:white;">&nbsp;</td>';
$html.='<td style="text-align:right;padding-right:5px;border:1px solid black;"><strong>'.@$balance_vat_included.'</strong></td>';
$html.='</tr>';
$html.='</table>';
$html.='</td></tr></table>';

if($partial_payments_num_rows > 0) {
	$html.='<table dir="'.$dir_table.'" style="'.$style_table.'"><tr><td>&nbsp;</td></tr><tr><td width="40%">&nbsp;</td><td style="font-size:16px;"><strong>תשלומים נוכחים</strong></td></tr></table>';
	$html.='<table dir="'.$dir_table.'" style="'.$style_table.'"><tr><td>&nbsp;</td></tr><tr><td width="4%">&nbsp;</td><td><table>';	
	$html.='<tr height="35px;" style="font-size:12px;font-weight:bold;background-color:silver;">';
	$html.='<th width="30px;" style="text-align:center;border:1px solid black!important;">&#x2116;</th>';
	$html.='<th width="80px;" style="text-align:center;border:1px solid black!important;">&nbsp;'.getLang('submit_date').'</th>';
	$html.='<th width="120px;" style="text-align:center;border:1px solid black!important;">&nbsp;'.getLang('description').'</th>';
	$html.='<th width="120px;" style="text-align:center;border:1px solid black!important;">&nbsp;'.getLang('submitted_account').'</th>';
	$html.='<th width="80px;;" style="text-align:center;border:1px solid black!important;">&nbsp;'.getLang('approval_date').'</th>';
	$html.='<th width="100px;;" style="text-align:center;border:1px solid black!important;">&nbsp;'.getLang('approved_amount').'</th>';
	$html.='<th width="80px;" style="text-align:center;border:1px solid black!important;">&nbsp;'.getLang('payment_date').'</th>';
	$html.='<th width="100px;" style="text-align:center;border:1px solid black!important;">&nbsp;'.getLang('paid_amount').'</th>';	
	$html.='<th width="50px;" style="text-align:center;border:1px solid black!important;">&nbsp;'.getLang('vat').'</th>';
	$html.='<th width="100px;" style="text-align:center;border:1px solid black!important;">&nbsp;'.getLang('order_balance').'</th>';
	$html.='<th width="80px;" style="text-align:center;border:1px solid black!important;">&nbsp;'.getLang('invoice_date').'</th>';													
	$html.='</tr>';
								
	$count = 0;
	foreach($partial_payments as $item) {
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
		
		$html.='<tr style="height:30px;font-size:12px;">';
		$html.='<td style="text-align:center;border:1px solid black!important;">'.@$count.'</td>';		
		$html.='<td style="text-align:left;padding-left:5px;border:1px solid black!important;">&nbsp;'.$submit_date.'</td>';									
		$html.='<td style="text-align:left;padding-left:5px;border:1px solid black!important;">&nbsp;'.@$item->description.'</td>';
		$html.='<td style="text-align:right;padding-right:5px;border:1px solid black!important;">&nbsp;'.@$submitted_account.'</td>';
		$html.='<td style="text-align:left;padding-left:5px;border:1px solid black!important;">&nbsp;'.$approval_date.'</td>'; 
		$html.='<td style="text-align:right;padding-right:5px;border:1px solid black!important;">&nbsp;'.@$approved_amount.'</td>';
		$html.='<td style="text-align:left;padding-left:5px;border:1px solid black!important;">&nbsp;'.$payment_date.'</td>';
		$html.='<td style="text-align:right;padding-right:5px;border:1px solid black!important;">&nbsp;'.$paid_amount_vat_included.'</td>';
		$html.='<td style="text-align:center;border:1px solid black!important;">&nbsp;'.number_format(@$item->vat,0,'.',',').'%</td>';
		$html.='<td style="text-align:center;border:1px solid black!important;">&nbsp;'.@$balance.'</td>';
		$html.='<td style="text-align:center;border:1px solid black!important;">&nbsp;'.@$invoice_date.'</td>';
		$html.='</tr>';
	}
	$html.='</table>';
	$html.='</td></tr></table>';
}

$html.='</div>';
$html.='</div>';

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

if($_SESSION['lang'] == "HE") 
	$pdf->setRTL(true);

$pdf->AddPage();

$pdf->setTextShadow(array('enabled'=>true, 'depth_w'=>0.2, 'depth_h'=>0.2, 'color'=>array(196,196,196), 'opacity'=>1, 'blend_mode'=>'Normal'));
$pdf->writeHTMLCell(0, 0, '', '', $html, 0, 1, 0, true, '', true);
ob_end_clean();
$pdf_name = substr(date('Y-m-d'),0,4).substr(date('Y-m-d'),5,2).substr(date('Y-m-d'),8,2).'-Orders Payments Report-'.$supplier->name.'.pdf';
$pdf->Output($pdf_name,'I');
?>