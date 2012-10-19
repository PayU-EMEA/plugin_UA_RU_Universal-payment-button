<?php
define("__MERCHANT__", "идентификатор_мерчанта"); 
define("__SECRETKEY__", "Секретный ключ"); 
define("__DEBUG__", 1); # Debug mode 0 = off, 1 = on  



define("__currency__", "UAH"); # Currency of merchant
define("__language__", "RU"); # Language of payment page

define("__PAYUIMG__", "/payu.jpg"); # link to file payu.jpg

define("__LU__", ""); # for UA live empty
//---------
define("__Encode__", "UTF-8"); # windows-1251 or utf-8 



//----------------
//-- DO NOT CHANGE

header("Content-type:text/html; charset=".__Encode__);

if ( isset($_GET['get']) )
{
	switch( $_GET['get'] )
	{
		case "all" :
		{ 
			?>
			document.write('<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js" type="text/javascript"></script>');
			document.write('<link rel="stylesheet" href="<?= $_SERVER['PHP_SELF'] ?>?get=css" type="text/css" media="all"/>');
			document.write('<script src="<?= $_SERVER['PHP_SELF'] ?>?get=js" type="text/javascript"></script>');
			<?
			break;
		}
		case "css" :
		{ 
			?>
				.payu_fade{ position:fixed; top:0px; left:0px; right:0px; bottom:0px; background-color:#000000; opacity:0.4; z-index:1000; }
				.payu_fadeblock{ position:fixed; top:40%; width:400px; height:150px; left:40%; margin:-100px 0px 0px -100px; z-index:1001; background-color:#ffffff; border:2px solid #009CDD; padding:10px; border-radius:5px; font-family: Georgia,"Times New Roman",Times,serif; font-size: 0.75em; line-height: 1.3; -webkit-border-radius:5px; -moz-border-radius:5px;}
				.payu_fadeblock input{ float:right; width:250px; }    
				.payu_fadeblock div{ margin:10px; }
				.payu_title{text-align:center; margin-top:10px; font-size:1.2em;}
				.payu_sender{ padding:5px;  border-radius:5px; -webkit-border-radius:5px; -moz-border-radius:5px; cursor:pointer; background-color:#aeaeae;}
			<?
			break;
		}

		case "js" :
		{ 
			?>
			jQuery.noConflict();
			jQuery(document).ready( function(){
    			jQuery("#PayU_paybutton").click( function(){ PayU_getForm(); } );
 			});

			function PayU_getForm()
			{
  			if( !jQuery(".payu_fade").length  ) jQuery("body").append("<div class='payu_fade' onclick='PayU_close()'>&nbsp;</div>");
 			jQuery.post( '<?= $_SERVER['PHP_SELF'] ?>?act=getForm', '', function(data){ jQuery("body").append("<div class='payu_fadeblock'>"+data+"</div>"); });
			}

			function PayU_close(){ jQuery(".payu_fadeblock, .payu_fade").remove(); }

			function PayU_sendForm()
			{
  			el = jQuery("#PayUForm input[name=sum]");
  			value = parseFloat(jQuery(el).val(), 2);
  			if ( isNaN(value) ) value = "";
  			jQuery(el).val(value);
  
  			if ( value == "" || value == 0 || jQuery("#PayUForm input[name=name]").val() == "" ){ alert("Не введены данные"); return; }
  			
  			jQuery.post( '<?= $_SERVER['PHP_SELF'] ?>?act=sendForm', jQuery("#PayUForm").serialize(), function(data){ jQuery('.payu_fadeblock').html( data ); });
			}
			<?
			break;
		}

	}
die();
}

//---------- END OF LOADERS --------------------


if ( !isset($_GET['act']) ) die();
$button = "<div style='left: 50%; margin: 50px 0 0 -70px; position: relative; top: 50%;'>".
          "<div><img src='".__PAYUIMG__ ."'></div></div>".
          "<script>
             setTimeout( subform, 100 );
             function subform(){ document.getElementById('PayUForm').submit(); }
           </script>";



$option  = array( 'merchant' => __MERCHANT__, 
				  'secretkey' => __SECRETKEY__, 
                  'debug' => __DEBUG__, 
                  "button" => $button );

if ( __LU__ != "" ) $option['luUrl'] = __LU__;


$forSend = array (
					'ORDER_PCODE' => array(1),
					'ORDER_PINFO' => array(""),
					'ORDER_QTY' => array(1),
					'ORDER_VAT' => array(0),
					'ORDER_SHIPPING' => 0,
					'PRICES_CURRENCY' => __currency__,
					'LANGUAGE' => __language__
				  );


switch( $_GET['act'] )
{
	case "sendForm" : 
	{
		$name = ( __Encode__ !=  "utf-8" ) ? iconv(  "UTF-8", __Encode__, $_POST['name']  ) : $_POST['name'];
		$forSend['ORDER_PNAME'] = array( $name );
		$forSend['ORDER_PRICE'] = array((float) $_POST['sum']);
		$pay = PayU::getInst()->setOptions( $option )->setData( $forSend )->LU();
		echo $pay;
		break;
	}
	case "getForm" : 
	{
		echo '<form id="PayUForm">'.
 			 '<div class="payu_title">Оплата товара</div>'.
	         '<div>Название товара : <input type="text" name="name" autocomplete="off" ></div>'.
	         '<div>Сумма : <input type="text" name="sum" autocomplete="off"></div>'.
	         '<div class="payu_title" style="margin:20px !important;" onclick="PayU_sendForm()"> <span class="payu_sender">Оплатить</span></div>'.
             '</form>';
	}
}


class PayU
{
	var $luUrl = "https://secure.payu.ua/order/lu.php", 
		$button = "<input type='submit'>",
		$debug = 0,
		$showinputs = "hidden";

	private static $Inst = false, $merchant, $key;

	private $data = array(), $dataArr = array(), $answer = ""; 
	private $LUcell = array( 'MERCHANT' => 1, 'ORDER_REF' => 0, 'ORDER_DATE' => 1, 'ORDER_PNAME' => 1, 'ORDER_PGROUP' => 0,
							'ORDER_PCODE' => 1, 'ORDER_PINFO' => 0, 'ORDER_PRICE' => 1, 'ORDER_QTY' => 1, 'ORDER_VAT' => 1, 
							'ORDER_SHIPPING' => 1, 'PRICES_CURRENCY' => 1, 'PAY_METHOD' => 0, 'ORDER_PRICE_TYPE' => 0);

	private $IPNcell = array( "IPN_PID", "IPN_PNAME", "IPN_DATE", "ORDERSTATUS" );

	private function __construct(){}
	private function __clone(){}
	public function __toString()
	{ 
		return ( $this->answer === "" ) ? "<!-- Answer are not exists -->" : $this->answer;  
	}
	public static function getInst()
	{	
		if( self::$Inst === false ) self::$Inst = new PayU();
		return self::$Inst;
	}
#---------------------------------------------
	function setOptions( $opt = array() )
	{
		if ( !isset( $opt['merchant'] ) || !isset( $opt['secretkey'] )) die("No params");
		self::$merchant = $opt['merchant'];
		self::$key = $opt['secretkey'];
		unset( $opt['merchant'], $opt['secretkey'] );
		if ( count($opt) === 0 ) return $this;
		foreach ( $opt as $k => $v) $this->$k = $v;
		return $this;
	}

	function setData( $array = null )
	{	
		if ($array === null ) die("No data");
		$this->dataArr = $array;
		return $this;
	}

	function Signature( $data = null ) 
	{		
		$str = "";
		foreach ( $data as $v ) $str .= $this->convData( $v );
		return hash_hmac("md5",$str, self::$key);
	}

	private function convString($string) 
	{	
		$string = iconv( "windows-1251", "UTF-8", $string );
		return mb_strlen($string, '8bit') . $string;
	}

	private function convArray($array) 
	{
  		$return = '';
  		foreach ($array as $v) $return .= $this->convString( $v );
  		return $return;
	}

	private function convData( $val )
	{
		return ( is_array( $val ) ) ? $this->convArray( $val ) : $this->convString( $val );
	}
//===
	public function LU()
	{	
		$arr = &$this->dataArr;
		$arr['MERCHANT'] = self::$merchant;
		if( !isset($arr['ORDER_DATE']) ) $arr['ORDER_DATE'] = date("Y-m-d H:i:s");
		$arr['TESTORDER'] = ( $this->debug == 1 ) ? "TRUE" : "FALSE";
		$arr['DEBUG'] = $this->debug;
		$arr['ORDER_HASH'] = $this->Signature( $this->checkArray( $arr ) );
		$this->answer = $this->genereteForm( $arr );
		return $this;
	}

	private function checkArray( $data )
	{
		$this->cells = array();
		$ret = array();
		foreach ( $this->LUcell as $k => $v ) 
		{ 	
			if ( isset($data[$k]) ) $ret[$k] = $data[$k];
			 elseif ( $v == 1 ) die("$k is not set");
		}
		return $ret;
	}

	private function genereteForm( $data )
	{	
		$form = '<form method="post" action="'.$this->luUrl.'" accept-charset="utf-8" id="PayUForm" >';
		foreach ( $data as $k => $v ) $form .= $this->makeString( $k, $v );
		return $form . $this->button."</form>";
	}	

	private function makeString ( $name, $val )
	{
		$str = "";
		if ( !is_array( $val ) ) return '<input type="'.$this->showinputs.'" name="'.$name.'" value="'.htmlspecialchars($val).'">'."\n";
		foreach ($val as $v) $str .= $this->makeString( $name.'[]', $v );
		return $str;
	}

//===

	public function IPN()
	{	
		$arr = &$this->dataArr;
		$arr = $_POST;
		foreach ( $this->IPNcell as $name ) if ( !isset( $arr[ $name ] ) ) die( "Incorrect data" );

		$hash = $arr["HASH"];  
		unset( $arr["HASH"] );
		$sign = $this->Signature( $arr );

		if ( $hash != $sign ) return $this;
		$datetime = date("YmdHis");
		$sign = $this->Signature(  array(
				   						"IPN_PID" => $arr[ "IPN_PID" ][0], 
				  						"IPN_PNAME" => $arr[ "IPN_PNAME" ][0], 
				   						"IPN_DATE" => $arr[ "IPN_DATE" ], 
				   						"DATE" => $datetime
										)
								);

		$this->answer = "<!-- <EPAYMENT>$datetime|$sign</EPAYMENT> -->";
		return $this;
	}

//===

	function checkBackRef( $type = "http")
	{
		$path = $type.'://'.$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'];
		$tmp = explode("?", $path);
		$url = $tmp[0].'?';
		$params = array();
		foreach ($_GET as $k => $v)
		{
			if ( $k != "ctrl" ) $params[] = $k.'='.rawurlencode($v);
		}
		$url = $url.implode("&", $params);
		$arr = array($url);
		$sign = $this->Signature( $arr );
		$this->answer = ( $sign === $_GET['ctrl'] ) ? true : false;
		return $this->answer;
	}
}

?>