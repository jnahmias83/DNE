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
$style_td = 'text-align:left;padding-left:12px;';
$name_sort = 's.name';
$title = @$project->name_he.'<br/>דוח הזמנות<br/>'.substr(date('Y-m-d'),8,2).'/'.substr(date('Y-m-d'),5,2).'/'.substr(date('Y-m-d'),0,4);

if($_SESSION['lang'] == 'HE') {
    $dir_table = 'rtl';
	$style_table = "margin-top:25px;margin-left:2.3%;";
	$style_td = 'text-align:right;padding-right:12px;';
	$name_sort = 's.name_he';
}

$query = $mysqli->prepare("SELECT o.id_projects_suppliers AS id_projects_suppliers,o.description AS description,o.signature_date AS signature_date,o.sum_order AS sum_order,
                          s.name AS name,s.name_he AS name_he,sfow.name AS sfow_name,sfow.name_he AS sfow_name_he
                          FROM dne_orders o
                          LEFT JOIN dne_projects_suppliers ps on o.id_projects_suppliers = ps.id
						  LEFT JOIN dne_suppliers s ON ps.id_supplier = s.id
						  LEFT JOIN dne_sup_field_of_work sfow ON s.id_field_of_work = sfow.id
						  WHERE ps.id_project = ?
						  ORDER BY o.signature_date DESC,s.type,".$name_sort);
$query->bind_param("i",$project_id);
$query->execute();
$query->store_result();
$orders = fetch($query);

$html = '<table width="90%" style="margin-top:15px;"><tr><td style="text-align:center;"><img src="images/davidnahmias_stripe.jpg" /><br/><br/></td></tr>';
$html.= '<tr><td style="text-align:center;padding-top:30px;"><span dir="'.$dir_table.'"><strong><u>'.$title.'</u></strong></span></td></tr></table>';
$html.= '<div class="row">';
$html.= '<div class="col-md-12">';
$html.= '<table dir="'.$dir_table.'" style="'.$style_table.'"><tr><td width="4%">&nbsp;</td><td><table cellpadding="4">';
$html.='<tr height="35px;" style="font-size:12px;font-weight:bold;background-color:silver;height:50px;">';
$html.='<th width="30px;" style="text-align:center;border:1px solid black;">#</th>';
$html.='<th width="70px;" style="text-align:center;border:1px solid black;">'.getLang('signature_date').'</th>';
$html.='<th width="180px;" style="text-align:center;border:1px solid black;">'.getLang('supplier_name').'</th>';
$html.='<th width="180px;" style="text-align:center;border:1px solid black;">'.getLang('supplier_domain').'</th>';
$html.='<th width="250px;" style="text-align:center;border:1px solid black;">'.getLang('description').'</th>';
$html.='<th width="120px;" style="text-align:center;border:1px solid black;">'.getLang('total_orders_h').'</th>';
$html.='</tr>';

$count = 0;

$total_sum_orders = 0;

foreach($orders as $item) { 
    $count++;
    $data_bg_color = '#ffffff';
	if($count%2 != 0) 
	   $data_bg_color = '#dedede';

	$signature_date = '';
	if($item->signature_date != '0000-00-00') 
		$signature_date = substr($item->signature_date,8,2).'/'.substr($item->signature_date,5,2).'/'.substr($item->signature_date,2,2);
	
    $html.='<tr height="30px;" style="font-size:12px;background-color:'.$data_bg_color.';">'; 
	$html.='<td width="30px;" style="text-align:center;border:1px solid black;">'.$count.'</td>';
	$html.='<td width="70px;" style="text-align:center;border:1px solid black;">&nbsp;'.$signature_date.'</td>';
	$html.='<td width="180px;" style="'.$style_td.'border:1px solid black;">';
	if($_SESSION['lang'] != 'HE')
		$html.=$item->name;
	 else 
		$html.=$item->name_he;
	 $html.='</td>';
	 $html.='<td width="180px;" style="'.$style_td.'border:1px solid black;">';
	 if($_SESSION['lang'] != 'HE')
		 $html.=$item->sfow_name;
	 else 
		 $html.=$item->sfow_name_he;
	$html.='</td>';
	$html.='<td width="250px;" style="'.$style_td.'border:1px solid black;">&nbsp;'.$item->description.'</td>';
	$html.='<td width="120px;" style="text-align:right;padding-right:12px;border:1px solid black;">'.number_format($item->sum_order,2,'.',',').'&nbsp;&#x20aa;</td>';
    $html.='</tr>';
	
	$total_sum_orders += $item->sum_order;
}

$total_sum_orders = number_format($total_sum_orders,2,'.',',');

$html.='<tr style="font-size:12px;"><td colspan="5" style="text-align:right;padding-right:12px;background-color:#dcf1fa;border:1px solid black;"><strong>'.getLang('total_orders').'</strong></td>';
$html.='<td style="text-align:right;background-color:#dcf1fa;border:1px solid black;"><strong>'.$total_sum_orders.'&nbsp;&#x20aa;</strong></td></tr>';
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
$pdf_name = substr(date('Y-m-d'),0,4).substr(date('Y-m-d'),5,2).substr(date('Y-m-d'),8,2).'-Orders Report-'.$project->nickname.'.pdf';
$pdf->Output($pdf_name,'I');
?>