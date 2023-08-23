<?php
require_once('tcpdf_min/config/tcpdf_config.php');
require_once('tcpdf_min/tcpdf.php');
include 'include/header.php';
include 'functions/functions.php';
error_reporting(E_ALL);
ini_set('display_errors', '1');

$sup_type = @$_GET['sup_type'];
$project_id = @$_GET['project_id'];

$query = $mysqli->prepare("SELECT * FROM dne_projects WHERE id = ?");
$query->bind_param("i",$project_id);
$query->execute();
$query->store_result();
$project = fetch_unique($query);

if($sup_type == 'S')
	$title_pdf = 'רשימת קבלנים וספקים';
else if($sup_type == 'D')
	$title_pdf = 'רשימת מתכננים ויועצים';

$dir_table = 'rtl';
$style_table = "margin-top:25px;margin-left:2.3%;";
$project_name = @$project->name_he;

$query = $mysqli->prepare("SELECT ps.id AS id,ps.id_project AS id_project,s.name_he AS name_he,s.phone AS phone,s.email_office AS email_office,sfow.name_he AS sfow_name_he
						  FROM dne_projects_suppliers AS ps 
						  LEFT JOIN dne_projects AS p ON ps.id_project = p.id 
						  LEFT JOIN dne_suppliers AS s ON ps.id_supplier = s.id
						  LEFT JOIN dne_sup_field_of_work sfow ON s.id_field_of_work = sfow.id
						  WHERE p.id = ? AND s.type = ?
						  ORDER BY sfow.name_he,s.name_he");
$query->bind_param("is",$project_id,$sup_type);
$query->execute(); 
$query->store_result();
$existing_sup_proj_num_rows = $query->num_rows;
$sup_proj = fetch($query);

$html = '<table width="100%" style="margin-top:15px;"><tr><td style="text-align:center;"><img src="images/davidnahmias_stripe.jpg" /><br/><br/></td></tr>';
$html.= '<tr><td style="text-align:center;padding-top:30px;"><span dir="'.$dir_table.'"><strong><u>'.$project_name.'<br/>'.$title_pdf.'<br/>'.substr(date('Y-m-d'),8,2).'/'.substr(date('Y-m-d'),5,2).'/'.substr(date('Y-m-d'),0,4).'</span></u></strong></td></tr></table>';
$html.= '<div class="row">';
$html.= '<div class="col-md-12">';
$html.= '<table dir="'.$dir_table.'" style="'.$style_table.'"><tr><td width="23%">&nbsp;</td><td><table cellpadding="4">';
$html.='<tr height="35px;" style="font-size:12px;font-weight:bold;background-color:silver;">';
$html.='<th width="30px;" style="text-align:center;border:1px solid black;">#</th>';
$html.='<th width="120px;" style="text-align:center;border:1px solid black;">תחום</th>';
$html.='<th width="150px;" style="text-align:center;border:1px solid black;">שם</th>';
$html.='<th width="100px;" style="text-align:center;border:1px solid black;">טלפון</th>';
$html.='<th width="160px;" style="text-align:center;border:1px solid black;">דוא\'\'ל</th>';
$html.='</tr>';

$count = 0;

foreach($sup_proj as $item) { 
    $count++;
	
    $html.='<tr height="30px;" style="font-size:12px;">'; 
	$html.='<td width="30px;" style="text-align:center;border:1px solid black;">'.$count.'</td>';
	$html.='<td width="120px;" style="text-align:right;padding-left:12px;border:1px solid black;">&nbsp;'.$item->sfow_name_he.'</td>';
	$html.='<td width="150px;" style="text-align:right;padding-left:12px;border:1px solid black;">&nbsp;'.$item->name_he.'</td>';
	$html.='<td width="100px;" style="text-align:center;border:1px solid black;">&nbsp;'.$item->phone.'</td>';
	$html.='<td width="160px;" style="text-align:center;border:1px solid black;">&nbsp;'.$item->email_office.'</td>';
    $html.='</tr>';
}
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

$pdf->setRTL(true);

$pdf->AddPage();

$pdf->setTextShadow(array('enabled'=>true, 'depth_w'=>0.2, 'depth_h'=>0.2, 'color'=>array(196,196,196), 'opacity'=>1, 'blend_mode'=>'Normal'));
$pdf->writeHTMLCell(0, 0, '', '', $html, 0, 1, 0, true, '', true);
ob_end_clean();

if($sup_type == 'S')
	$pdf_name = substr(date('Y-m-d'),0,4).substr(date('Y-m-d'),5,2).substr(date('Y-m-d'),8,2).'-Suppliers List-'.$project->nickname.'.pdf';
else if($sup_type == 'D')
	$pdf_name = substr(date('Y-m-d'),0,4).substr(date('Y-m-d'),5,2).substr(date('Y-m-d'),8,2).'-Designers List-'.$project->nickname.'.pdf';
$pdf->Output($pdf_name,'I');
?>