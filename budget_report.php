<?php
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
$title = @$project->name_he.'<br/>דוח תקציב<br/>'.substr(date('Y-m-d'),8,2).'/'.substr(date('Y-m-d'),5,2).'/'.substr(date('Y-m-d'),0,4);

if($_SESSION['lang'] == 'HE') {
    $dir_table = 'rtl';
	$style_table = "margin-top:12px;margin-left:4%;";
	$style_td = "text-align:right;padding-right:12px;";
	$style_td_totals = "text-align:left;padding-left:12px;";
	$name_sort = 's.name_he';
}

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

$count = 0;

$html = '<table width="100%" style="margin-top:15px;"><tr><td style="text-align:center;"><img src="images/davidnahmias_stripe.jpg" /><br/><br/></td></tr>';
$html.= '<tr><td width="12px;">&nbsp;</td><td style="text-align:center;padding-top:30px;"><span dir="'.$dir_table.'"><strong><u>'.$title.'</u></strong></span></td></tr></table>';
$html.= '<div class="row">';
$html.= '<div class="col-md-12">';
$html.= '<table dir="'.$dir_table.'" style="'.$style_table.'"><tr><td width="4%">&nbsp;</td><td><table cellpadding="4">';
$html.='<tr height="35px;" style="font-size:12px;font-weight:bold;">';
$html.='<th colspan="3" style="text-align:center;background-color:silver;border:1px solid black;" width="350px;">'.getLang('supplier').'</th>';
$html.='<th colspan="3" width="330px;" style="text-align:center;border:1px solid black;">'.getLang('ht').'</th>';
$html.='<th width="20px;" style="background-color:white;">&nbsp;</th>';
$html.='<th colspan="2" width="220px;" style="text-align:center;border:1px solid black;background-color:#dcf1fa;">'.getLang('ttc').'</th>';
$html.='</tr>';
$html.='<tr height="35px;" style="font-size:12px;font-weight:bold;">';
$html.='<th width="30px;" style="text-align:center;border:1px solid black;">#</th>';
$html.='<th width="160px;" style="text-align:center;border:1px solid black;">'.getLang('supplier_name').'</th>';
$html.='<th width="160px;" style="text-align:center;border:1px solid black;">'.getLang('supplier_domain').'</th>';
$html.='<th width="110px;" style="text-align:center;border:1px solid black;">'.getLang('total_orders').'</th>';
$html.='<th width="110px;" style="text-align:center;border:1px solid black;">'.getLang('paid_amount').'</th>';
$html.='<th width="110px;" style="text-align:center;border:1px solid black;">'.getLang('order_balance').'</th>';
$html.='<th width="20px" style="background-color:white;">&nbsp;</th>';
$html.='<th width="110px;" style="text-align:center;border:1px solid black;">'.getLang('order_balance').'</th>';
$html.='<th width="110px;" style="text-align:center;border:1px solid black;">'.getLang('account_to_be_paid').'</th>';
$html.='</tr>';

foreach($orders as $item) { 
    $count++;
	
    $data_bg_color = '#ffffff';
	if($count%2 != 0) 
	   $data_bg_color = '#dedede';
   
    $query = $mysqli->prepare("SELECT SUM(p.paid_amount_vat_excluded) AS sum_paid_amount,SUM(p.approved_amount)-SUM(p.paid_amount) AS account_to_be_paid,
							  s.name AS name,s.name_he AS name_he,s.id_field_of_work AS id_field_of_work,sfow.name AS sfow_name,sfow.name_he AS sfow_name_he
							  FROM dne_payments p
							  LEFT JOIN dne_projects_suppliers ps on p.id_projects_suppliers = ps.id 
							  LEFT JOIN dne_suppliers s ON ps.id_supplier = s.id
							  LEFT JOIN dne_sup_field_of_work sfow ON s.id_field_of_work = sfow.id
							  WHERE ps.id = ? ORDER BY s.type,".$name_sort);
    $query->bind_param("i",$item->id_projects_suppliers);
    $query->execute();
    $query->store_result();
    $payment = fetch_unique($query);
	
    $total_sum_order_elem_display = '';
	if($item->total_sum_order > 0) 
	   $total_sum_order_elem_display = number_format($item->total_sum_order,2,'.',',').'&#8362;';

	$sum_paid_amount_display = '';
	if($payment->sum_paid_amount > 0) 
	   $sum_paid_amount_display = number_format($payment->sum_paid_amount,2,'.',',').'&#8362;';
	
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
	
    $html.='<tr height="30px;" style="font-size:12px;background-color:'.$data_bg_color.';">'; 
	$html.='<td width="30px;" style="text-align:center;border:1px solid black;">'.$count.'</td>';
	$html.='<td width="160px;" style="'.$style_td.'border:1px solid black;">&nbsp;';
	if($_SESSION['lang'] != 'HE') 
	    $html.=@$payment->name;
	else 
		$html.=@$payment->name_he;
	$html.='</td>';
	$html.='<td width="160px;" style="'.$style_td.'border:1px solid black;">';
	if($_SESSION['lang'] != 'HE') 
	    $html.=@$payment->sfow_name;
	else 
		$html.=@$payment->sfow_name_he;
	$html.='</td>';
	$html.='<td width="110px;" style="text-align:right;padding-right:12px;border:1px solid black;">'.@$total_sum_order_elem_display.'</td>';
	$html.='<td width="110px;" style="text-align:right;padding-right:12px;border:1px solid black;">'.@$sum_paid_amount_display.'</td>';
	$html.='<td width="110px;" style="text-align:right;padding-right:12px;border:1px solid black;">'.number_format($balance,0,'.', ',').'&nbsp;&#x20aa;</td>';
	$html.='<td style="background-color:white;">&nbsp;</td>';
	$html.='<td width="110px;" style="text-align:right;padding-right:12px;border:1px solid black;">'.number_format($balance_vat_included,0,'.',',').'&nbsp;&#x20aa;</td>';
	$html.='<td width="110px;" style="text-align:right;padding-right:12px;border:1px solid black;">'.$account_to_be_paid_display.'</td>';
    $html.='</tr>';
}

$total_to_pay_display = '';
if($total_to_pay != 0)
	$total_to_pay_display = number_format($total_to_pay,0,'.',',').'&nbsp;&#x20aa;';

$html.='<tr>';
$html.='<td colspan="9">&nbsp;</td>';
$html.='</tr>';

$html.='<tr><td colspan="9">&nbsp;</td></tr>';
$html.='<tr height="35px;" style="font-size:12px;font-weight:bold;background-color:silver;">';
$html.='<td colspan="3" style="'.$style_td_totals.'border:1px solid black;">'.getLang('total_ht').'</td>';
$html.='<td style="text-align:right;padding-right:12px;border:1px solid black;">'.number_format($total_sum_order,0,'.',',').'&nbsp;&#x20aa;</td>';
$html.='<td style="text-align:right;padding-right:12px;border:1px solid black;">'.number_format($total_sum_paid,0,'.',',').'&nbsp;&#x20aa;</td>';
$html.='<td style="text-align:right;padding-right:12px;border:1px solid black;">'.number_format($total_balance,0,'.',',').'&nbsp;&#x20aa;</td>';
$html.='<td style="background-color:white;">&nbsp;</td>';
$html.='<td style="text-align:right;padding-right:12px;background-color:#dcf1fa;border:1px solid black;">'.getLang('total_balance').'</td>';
$html.='<td style="text-align:right;padding-right:12px;background-color:#dcf1fa;border:1px solid black;">'.getLang('total_to_be_paid').'</td>';
$html.='</tr>';

$html.='<tr height="24px;" style="font-size:12px;font-weight:bold;background-color:#dcf1fa;">';
$html.='<td colspan="3" style="'.$style_td_totals.'border:1px solid black;">'.getLang('total_ttc').'</td>';
$html.='<td style="text-align:right;padding-right:12px;border:1px solid black;">'.number_format($total_sum_order_vat_included,0,'.',',').'&nbsp;&#x20aa;</td>';
$html.='<td style="text-align:right;padding-right:12px;border:1px solid black;">'.number_format($total_sum_paid_vat_included,0,'.',',').'&nbsp;&#x20aa;</td>';
$html.='<td style="text-align:right;padding-right:12px;border:1px solid black;">'.number_format($total_balance_vat_included,0,'.',',').'&nbsp;&#x20aa;</td>';
$html.='<td style="background-color:white;">&nbsp;</td>';
$html.='<td style="text-align:right;padding-right:12px;background-color:#dcf1fa;border:1px solid black;">'.number_format($total_balance_vat_included,0,'.',',').'&nbsp;&#x20aa;</td>';
$html.='<td style="text-align:right;padding-right:12px;background-color:#dcf1fa;border:1px solid black;">'.$total_to_pay_display.'</td>';
$html.='</tr>';
$html.='</table></td></tr></table>';
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
$pdf_name = substr(date('Y-m-d'),0,4).substr(date('Y-m-d'),5,2).substr(date('Y-m-d'),8,2).'-Budget Report-'.$project->nickname.'.pdf';
$pdf->Output($pdf_name,'I');
?>