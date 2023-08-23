<?php
session_start();
require_once('tcpdf_min/config/tcpdf_config.php');
require_once('tcpdf_min/tcpdf.php');
include 'include/header.php';
include 'functions/functions.php';
error_reporting(E_ALL);
ini_set('display_errors', '1');

$project_id = @$_GET['project_id'];

$query = $mysqli->prepare("SELECT * FROM dne_projects WHERE id = ?");
$query->bind_param("i",$project_id);
$query->execute();
$query->store_result();
$project = fetch_unique($query);

$_SESSION['lang'] = $project->lang;

$dir_table = 'ltr';
$style_table = "margin-top:25px;";
$style_td = "text-align:left;padding-left:12px;";
$title = @$project->name_he.'<br/>דוח תשלומים<br/>'.substr(date('Y-m-d'),8,2).'/'.substr(date('Y-m-d'),5,2).'/'.substr(date('Y-m-d'),0,4);
$name_sort = 's.name';

if($_SESSION['lang'] == 'HE') {
    $dir_table = 'rtl';
	$style_table = "margin-top:25px;margin-left:1%;";
	$style_td = "text-align:right;padding-right:12px;";
	$name_sort = 's.name_he';
}

$isTotalPayment = 1;		  
$query = $mysqli->prepare("SELECT p.id AS id,p.id_projects_suppliers AS id_projects_suppliers,p.description AS description,
						  p.submit_date AS submit_date,p.pdf_submission AS pdf_submission,p.submitted_account AS submitted_account,p.approval_date AS approval_date,
						  p.pdf_approval AS pdf_approval,p.approved_amount AS approved_amount,p.payment_date As payment_date,p.pdf_payment AS pdf_payment,
					      p.paid_amount AS paid_amount_vat_included,p.vat AS vat,p.paid_amount_vat_excluded AS paid_amount_vat_excluded,
						  p.invoice_date AS invoice_date,p.pdf_invoice AS pdf_invoice,s.nickname AS nickname				  
						  FROM dne_payments p
						  LEFT JOIN dne_projects_suppliers ps on p.id_projects_suppliers = ps.id
						  LEFT JOIN dne_suppliers s ON ps.id_supplier = s.id
						  WHERE p.IsTotalPayment = ? AND ps.id_project = ? ORDER BY p.payment_date DESC");
$query->bind_param("ii",$isTotalPayment,$project_id);
$query->execute(); 
$query->store_result();
$total_payments_num_rows = $query->num_rows;
$total_payments = fetch($query);

$isPartialPayment = 0;		  
$query = $mysqli->prepare("SELECT p.id AS id,p.id_projects_suppliers AS id_projects_suppliers,p.description AS description,
						  p.submit_date AS submit_date,p.pdf_submission AS pdf_submission,p.submitted_account AS submitted_account,p.approval_date AS approval_date,
						  p.pdf_approval AS pdf_approval,p.approved_amount AS approved_amount,p.payment_date As payment_date,p.pdf_payment AS pdf_payment,
					      p.paid_amount AS paid_amount_vat_included,p.vat AS vat,p.paid_amount_vat_excluded AS paid_amount_vat_excluded,
						  p.invoice_date AS invoice_date,p.pdf_invoice AS pdf_invoice,s.nickname AS nickname				  
						  FROM dne_payments p
						  LEFT JOIN dne_projects_suppliers ps on p.id_projects_suppliers = ps.id
						  LEFT JOIN dne_suppliers s ON ps.id_supplier = s.id
						  WHERE p.IsTotalPayment = ? AND ps.id_project = ? ORDER BY p.approval_date DESC");
$query->bind_param("ii",$isPartialPayment,$project_id);
$query->execute(); 
$query->store_result();
$partial_payments_num_rows = $query->num_rows;
$partial_payments = fetch($query);

$html = '<table width="100%" style="margin-top:15px;"><tr><td style="text-align:center;"><img src="images/davidnahmias_stripe.jpg" /><br/><br/></td></tr>';
$html.= '<tr><td width="15px;">&nbsp;</td><td style="text-align:center;padding-top:30px;"><span dir="'.$dir_table.'"><strong><u>'.$title.'</u></strong></span></td></tr></table>';
$html.= '<div class="row">';
$html.= '<div class="col-md-12">';
$html.= '<table dir="'.$dir_table.'" style="'.$style_table.'"><tr><td width="4%">&nbsp;</td><td><table cellpadding="4">';
$html.='<tr height="30px" style="font-size:12px;font-weight:bold;background-color:silver;">';
$html.='<th width="30px;" style="text-align:center;border:1px solid black;">#</th>';
$html.='<th width="90px;" style="text-align:center;border:1px solid black;">'.getLang('supplier_name').'</th>';
$html.='<th width="70px;" style="text-align:center;border:1px solid black;">'.getLang('submit_date').'</th>';
$html.='<th width="120px;" style="text-align:center;border:1px solid black;">'.getLang('description').'</th>';
$html.='<th width="100px;" style="text-align:center;border:1px solid black;">'.getLang('submitted_account').'</th>';
$html.='<th width="70px;" style="text-align:center;border:1px solid black;">'.getLang('approval_date').'</th>';
$html.='<th width="100px;" style="text-align:center;border:1px solid black;">'.getLang('approved_amount').'</th>';
$html.='<th width="70px;" style="text-align:center;border:1px solid black;">'.getLang('payment_date').'</th>';
$html.='<th width="100px;" style="text-align:center;border:1px solid black;">'.getLang('paid_amount').'</th>';
$html.='<th width="50px;" style="text-align:center;border:1px solid black;">'.getLang('vat').'</th>';
$html.='<th width="120px;" style="text-align:center;border:1px solid black;">'.getLang('order_balance').'</th>';
$html.='<th width="70x;" style="text-align:center;border:1px solid black;">'.getLang('invoice_date').'</th>';
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
	
    $html.='<tr height="30px;" style="font-size:12px;">';
	$html.='<td width="30px;" style="text-align:center;border:1px solid black;">'.$count.'</td>';
	$html.='<td width="90px;" style="'.$style_td.'border:1px solid black;">'.$item->nickname.'</td>';
	$html.='<td width="70px;" style="text-align:left;padding-left:12px;border:1px solid black;">&nbsp;'.$submit_date.'</td>';
	$html.='<td width="120px;" style="'.$style_td.'border:1px solid black;">'.$item->description.'</td>';	
    $html.='<td width="100px;" style="text-align:right;padding-right:12px;border:1px solid black;">'.$submitted_account.'</td>';
	$html.='<td width="70px;" style="text-align:left;padding-left:12px;border:1px solid black;">&nbsp;'.$approval_date.'</td>';   
    $html.='<td width="100px;" style="text-align:right;padding-right:12px;border:1px solid black;">&nbsp;'.$approved_amount.'</td>';	
	$html.='<td width="70px;" style="text-align:left;padding-left:12px;border:1px solid black;">&nbsp;'.$payment_date.'</td>';
	$html.='<td width="100px;" style="text-align:right;padding-right:12px;border:1px solid black;">'.$paid_amount_vat_included.'</td>';
	$html.='<td width="50px;" style="text-align:right;padding-right:12px;border:1px solid black;">'.number_format($item->vat,0,'.',',').'&nbsp;%</td>';
	$html.='<td width="120px;" style="text-align:center;border:1px solid black;">'.@$balance.'</td>';
    $html.='<td width="70px;" style="text-align:left;padding-left:12px;border:1px solid black;">&nbsp;'.$invoice_date.'</td>';
	$html.='</tr>';
}

foreach($total_payments as $item) { 
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
	
    $html.='<tr height="30px;" style="font-size:12px;background-color:#dedede;">';
	$html.='<td width="30px;" style="text-align:center;border:1px solid black;">'.$count.'</td>';
	$html.='<td width="90px;" style="'.$style_td.'border:1px solid black;">'.$item->nickname.'</td>';
	$html.='<td width="70px;" style="text-align:left;padding-left:12px;border:1px solid black;">&nbsp;'.$submit_date.'</td>';
	$html.='<td width="120px;" style="'.$style_td.'border:1px solid black;">'.$item->description.'</td>';	
    $html.='<td width="100px;" style="text-align:right;padding-right:12px;border:1px solid black;">'.$submitted_account.'</td>';
	$html.='<td width="70px;" style="text-align:left;padding-left:12px;border:1px solid black;">&nbsp;'.$approval_date.'</td>';   
    $html.='<td width="100px;" style="text-align:right;padding-right:12px;border:1px solid black;">&nbsp;'.$approved_amount.'</td>';	
	$html.='<td width="70px;" style="text-align:left;padding-left:12px;border:1px solid black;">&nbsp;'.$payment_date.'</td>';
	$html.='<td width="100px;" style="text-align:right;padding-right:12px;border:1px solid black;">'.$paid_amount_vat_included.'</td>';
	$html.='<td width="50px;" style="text-align:right;padding-right:12px;border:1px solid black;">'.number_format($item->vat,0,'.',',').'&nbsp;%</td>';	
	$html.='<td width="120px;" style="text-align:center;border:1px solid black;">'.@$balance.'</td>';
    $html.='<td width="70px;" style="text-align:left;padding-left:12px;border:1px solid black;">&nbsp;'.$invoice_date.'</td>';
	$html.='</tr>';
}

$html.='</table></td></tr></table>';
$html.='<br" /></div>';
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
$pdf_name = substr(date('Y-m-d'),0,4).substr(date('Y-m-d'),5,2).substr(date('Y-m-d'),8,2).'-Payments Report-'.$project->nickname.'.pdf';
$pdf->Output($pdf_name,'I');
?>