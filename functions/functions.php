<?php 
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$mysqli = new mysqli('localhost','davidnahmias','JmDn27091975','dn-engineering');	
$mysqli->set_charset("utf8");

function fetch($result)
{    
    $array = array();
    
    if($result instanceof mysqli_stmt)
    {
		$result->execute();
		$result->store_result();
        
        $variables = array();
        $data = array();
        $meta = $result->result_metadata();
        
        while($field = $meta->fetch_field())
            $variables[] = &$data[$field->name];
        
        call_user_func_array(array($result, 'bind_result'), $variables);
        
 
		$array = new stdClass();
        $i=0;
		while($result->fetch())
		{
			$array->$i = new stdClass();
			foreach($data as $k=>$v) $array->$i->$k = $v;
			$i++;
		}
    }
    elseif($result instanceof mysqli_result)
    {
		$array = new stdClass();
        $i=0;
		while($row = $result->fetch_assoc())
		{
			$array->$i = new stdClass();
			foreach($row as $k=>$v) $array->$i->$k = $v;
			$i++;
		}
    }
    return $array;
}

function fetch_unique($result)
{    
    $array = array();
    
    if($result instanceof mysqli_stmt)
    {  
		$result->execute();
		$result->store_result();
        
        $variables = array();
        $data = array();
        $meta = $result->result_metadata();
        
        while($field = $meta->fetch_field())
            $variables[] = &$data[$field->name];
        
        call_user_func_array(array($result, 'bind_result'), $variables);
        
		$array = new stdClass();
        $i=0;
		while($result->fetch())
		{
			$array->$i = new stdClass();
			foreach($data as $k=>$v) $array->$i->$k = $v;
			$i++;
		}
		if($i==1) {
			$new_object = new stdClass();
			$array=current( (Array)$array );
		} 
    }
    elseif($result instanceof mysqli_result)
    {
		$array = new stdClass();
        $i=0;
		while($row = $result->fetch_assoc())
		{
			$array->$i = new stdClass();
			foreach($row as $k=>$v) $array->$i->$k = $v;
			$i++;
		}
        if($i==1) {
			$new_object = new stdClass();
			$array=current( (Array)$array );
		} 
    }
    
	
    return $array;
}

function convert_array_to_obj_recursive($a) {
    if (is_array($a) ) {
        foreach($a as $k => $v) {
            if (is_integer($k)) {
                $a['index'][$k] = convert_array_to_obj_recursive($v);
            }
            else {
                $a[$k] = convert_array_to_obj_recursive($v);
            }
        }

        return (object) $a;
    }

    return $a; 
}

function get_date_name($date) {
	$tab = explode("-",$date);
	$annee = $tab[0];
	$mois = $tab[1];

	if($mois=='01')$mois='Janvier';
	if($mois=='02')$mois='Février';
	if($mois=='03')$mois='Mars';
	if($mois=='04')$mois='Avril';
	if($mois=='05')$mois='Mai';
	if($mois=='06')$mois='Juin';
	if($mois=='07')$mois='Juillet';
	if($mois=='08')$mois='Aout';
	if($mois=='09')$mois='Septembre';
	if($mois=='10')$mois='Octobre';
	if($mois=='11')$mois='Novembre';
	if($mois=='12')$mois='Décembre'; 
	
	return $mois." ".$annee;
}

function get_month($date) {
	$tab = explode("-",$date);
	$annee = $tab[0];
	$mois = $tab[1];

	if($mois=='01')$mois='Janvier';
	if($mois=='02')$mois='Février';
	if($mois=='03')$mois='Mars';
	if($mois=='04')$mois='Avril';
	if($mois=='05')$mois='Mai';
	if($mois=='06')$mois='Juin';
	if($mois=='07')$mois='Juillet';
	if($mois=='08')$mois='Aout';
	if($mois=='09')$mois='Septembre';
	if($mois=='10')$mois='Octobre';
	if($mois=='11')$mois='Novembre';
	if($mois=='12')$mois='Décembre'; 
	
	return $mois;
}

function get_year($date) {
	$tab = explode("-",$date);
	$annee = $tab[0];
	return $annee;
}

if (!function_exists('cleanCut')) {
    function cleanCut($string,$length,$cutString = '...') {
		if(strlen($string) <= $length) {
			return $string;
		}
		$str = substr($string,0,$length-strlen($cutString)+1);
		return substr($str,0,strrpos($str,' ')).$cutString;
    }	
}	

if (!function_exists('cleanChaine')) {
    function cleanChaine($String) {
        $Search = array("\\n", "\\r", "\n", "\r", "     ", "    ", "&amp;", " ", "        ", "       ", "      ", "     ", "    ", "   ", "  ", "à", "á", "â", "à", "À", "ç", "ç", "Ç", "é", "è", "ê", "ë", "É", "È", "é", "è", "ê", "í", "ï", "ï", "î", "ñ", "ô", "ò", "ö", "ô", "ó", "Ó", "ù", "û", ";");
        $Replace = array("-","-","-","-", "-", "", "-", "-", "-", "-", "-", "-", "-", "-", "-", "a", "a", "a", "a", "A", "c", "c", "C", "e", "e", "e", "e", "E", "E", "e", "e", "e", "i", "i", "i", "n", "o", "o", "o", "o", "o", "O", "u", "u", "\;");
        $String = str_replace($Search, $Replace, $String);
        $String = str_replace("'", "-", $String);
        $String = str_replace("\\", "-", $String);
            $String=str_replace('!','-',$String);
            $String=str_replace(':','-',$String);
            $String=str_replace('?','-',$String);
            $String=str_replace('’','',$String);
            $String=str_replace(',','',$String);
            $String=str_replace('.','',$String);
            $String=str_replace('---','-',$String);
            $String=str_replace('--','-',$String);
            $String=str_replace('%','',$String);
            $String=rtrim($String,'---');
            $String=rtrim($String,'--');
            $String=rtrim($String,'-');
            $String= strtolower($String);
        return $String;
    }
}

function encryptIt($str) {
    $cryptKey = 'qJB0rGtIn5UB1xG03efyCp';
    $str_encoded = base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_256, md5($cryptKey), $str, MCRYPT_MODE_CBC, md5(md5($cryptKey))));
    return($str_encoded);
}

function decryptIt($str) {
    $cryptKey = 'qJB0rGtIn5UB1xG03efyCp';
    $str_decoded = rtrim(mcrypt_decrypt(MCRYPT_RIJNDAEL_256, md5($cryptKey), base64_decode($str), MCRYPT_MODE_CBC, md5(md5($cryptKey))), "\0");
    return($str_decoded);
}

function php_mailer($fromEmail,$fromName,$to,$subject,$message,$attachment) {	
	$mail = new PHPMailer(true);
	$mail->SMTPDebug = 2;  
	$mail->CharSet = 'UTF-8';      
	$mail->Host = 'smtp.gmail.com';  
	$mail->SMTPAuth = true;                              
	$mail->Username = 'ophtalweb@ophtalmic-compagnie.fr';                 
	$mail->Password = 'fs254d5sfs';                         
	$mail->SMTPSecure = 'tls';                          
	$mail->Port = 587;                                    

	$mail->setFrom($fromEmail,$fromName);     
	$mail->addAddress($to);              
	$mail->isHTML(true);                                 
	$mail->Subject = $subject;
	$mail->Body = $message;
	$mail->AddAttachment($attachment);

	if(!$mail->send()) {
		echo 'Mailer Error: ' . $mail->ErrorInfo;
		return 0;
	}
	return 1;	
}

function compressImage($source, $destination, $quality) { 
    // Get image info 
    $imgInfo = getimagesize($source); 
    $mime = $imgInfo['mime']; 
     
    // Create a new image from file 
    switch($mime){ 
        case 'image/jpeg': 
            $image = imagecreatefromjpeg($source); 
           imagejpeg($image, $destination, $quality);
            break; 
        case 'image/png': 
            $image = imagecreatefrompng($source); 
            imagepng($image, $destination, $quality);
            break; 
        case 'image/gif': 
            $image = imagecreatefromgif($source); 
            imagegif($image, $destination, $quality);
            break; 
        default: 
            $image = imagecreatefromjpeg($source); 
           imagejpeg($image, $destination, $quality);
    } 
     
    // Return compressed image 
    return $destination; 
} 

function getLang($code){
	$mysqli = new mysqli('localhost','davidnahmias','JmDn27091975','dn-engineering');
	$mysqli->set_charset("utf8");	
	$query = $mysqli->prepare("SELECT ".$_SESSION['lang']." AS result FROM dne_lang WHERE code='".$code."'");
	$query->execute();
    $query->store_result();
    $query = fetch_unique($query);
	return $query->result;
}

function toAlpha($data){
    $alphabet =   array('a','b','c','d','e','f','g','h','i','j','k','l','m','n','o','p','q','r','s','t','u','v','w','x','y','z');
    $alpha_flip = array_flip($alphabet);
    if($data <= 25){
      return $alphabet[$data];
    }
    elseif($data > 25){
      $dividend = ($data + 1);
      $alpha = '';
      $modulo;
      while ($dividend > 0){
        $modulo = ($dividend - 1) % 26;
        $alpha = $alphabet[$modulo] . $alpha;
        $dividend = floor((($dividend - $modulo) / 26));
      } 
      return $alpha;
    }
}
?>