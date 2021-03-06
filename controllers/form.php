<?php
function form__controller() {
	wbTrigger("func",__FUNCTION__,"before");
	$call=__FUNCTION__ ."__".$_ENV["route"]["mode"];
	if (is_callable($call)) {
		$_ENV["DOM"]=$call();
	} else {
        if (form__controller__common__controller()==false) {
		  echo __FUNCTION__ .": отсутствует функция ".$call."()";
		  die;
        }
	}
	wbTrigger("func",__FUNCTION__,"after");
return $_ENV["DOM"];
}

function form__controller__common__controller() {
	$mode=$_ENV["route"]["mode"];
    $form=$_ENV["route"]["form"];
    $item=$_ENV["route"]["item"];
	$aCall=$form."_".$mode; $eCall=$form."__".$mode;
    $out=false;
	if (is_callable($aCall)) {$out=$aCall($item);} elseif (is_callable($eCall) AND $engine!==false) {$out=$eCall($item);}
    if ($out==false) {return false;} else {
        if (!is_object($out)) {$out=wbFromString($out);}
        $_ENV["DOM"]=$out;
    }
    return true;
}

function form__controller__show() {
	$form=$_ENV["route"]["form"];
    $item=$_ENV["route"]["item"];
	$mode="show";
    $Item=wbItemRead($form,$item);
	$aCall=$form."_".$mode; $eCall=$form."__".$mode;
	if (is_callable($aCall)) {$out=$aCall();} elseif (is_callable($eCall) AND $engine!==false) {$out=$eCall($Item);}
    if ($Item==false OR $Item["active"]!=="on") {
		echo form__controller__error_404();
		die;
	} else {
        if (isset($out)) {
            $_ENV["DOM"]=wbFromString($out);
        } else {
            $out=wbGetForm($form,$mode);
            if ($_ENV["error"]["wbGetForm"]!=="noform") { $_ENV["DOM"]=$out; } else {
                if (!isset($Item["template"]) OR $Item["template"]=="") {$Item["template"]="default.php";}
                if (is_callable("wbBeforeShowItem")) {$Item=wbBeforeShowItem($Item);}
                $_ENV["DOM"]=wbGetTpl($Item["template"]);
                if (!is_object($_ENV["DOM"])) {$_ENV["DOM"]=wbFromString($_ENV["DOM"]);}
            }
        }
		$_ENV["DOM"]->wbSetData($Item);
	}
	return $_ENV["DOM"];
}

function form__controller__remove() {
	if (isset($_SESSION["user_id"])) {
		$_ENV["DOM"]=wbGetForm("common","remove_confirm");
        if (isset($_REQUEST["confirm"]) AND $_REQUEST["confirm"]=="true") {
			$_ENV["DOM"]->find("script[data-wb-tag=success]")->remove();
		} else {
			wbItemRemove($_ENV["route"]["form"],$_ENV["route"]["item"]);
			$_ENV["DOM"]->find(".modal")->remove();
		}
		return $_ENV["DOM"];
	}
}

function form__controller__error_404($id=null) {
	header("HTTP/1.0 404 Not Found");
	return wbGetTpl("404.php");
}

function form__controller__list() {
	$form=$_ENV["route"]["form"];
	$mode=$_ENV["route"]["mode"];
	$aCall=$form."_".$mode; $eCall=$form."__".$mode;
	if (is_callable($aCall)) {$out=$aCall();} elseif (is_callable($eCall) AND $engine!==false) {$out=$eCall();}
	if (!isset($out)) {
		$_ENV["DOM"]=wbGetForm($form,$mode);
		$_ENV["DOM"]->wbSetData();
	} else {
		if (is_string($out)) {$out=wbFromString($out);}
		$_ENV["DOM"]=$out;
	}
	return $_ENV["DOM"];
}

function form__controller__edit() {
	$form=$_ENV["route"]["form"];
	$item=$_ENV["route"]["item"];
	$mode=$_ENV["route"]["mode"];
	$aCall=$form."_".$mode; $eCall=$form."__".$mode;
	if (is_callable($aCall)) {$out=$aCall();} elseif (is_callable($eCall) AND $engine!==false) {$out=$eCall();}
	if (!isset($out)) {
		$_ENV["DOM"]=wbGetForm($form,$mode);
		$data=wbItemRead(wbTable($form),$item);
		if (!$data) {$data=array("id"=>wbNewId());}
		$_ENV["DOM"]->wbSetData($data);
	} else {
		if (is_string($out)) {$out=wbFromString($out);}
		$_ENV["DOM"]=$out;
	}
	return $_ENV["DOM"];
}

function form__controller__default_mode() {
	if (!is_dir($_ENV["path_app"]."/forms")) {form__controller__setup_engine();} else {die("Установка выполнена");}
}

function form__controller__select2() {
    $form=""; $where=""; $tpl=""; $val="";
    $form=$_ENV["route"]["form"];
    $custom="form_controller_select2_{$form}"; 
    if (is_callable($custom)) {return $custom();} else {
        $result=array();
        $single=false;
        if (isset($_POST["where"]) AND $_POST["where"]>"") {$where=$_POST["where"];}
        if (isset($_POST["tpl"]) AND $_POST["tpl"]>"") {$tpl=$_POST["tpl"];}
        if (isset($_POST["value"]) AND $_POST["value"]>"") {$val=$_POST["value"];}
        if (isset($_POST["id"]) AND $_POST["id"]>"") {$where='id="'.$_POST["id"].'"'; $single=true;}
        $list=wbItemList($form,$where);
        $iterator=new ArrayIterator($list);
        foreach($iterator as $key => $r) {
            $data=wbSetValuesStr(strip_tags($tpl),$r);
            $res=preg_match("/{$val}/ui",$data);
            if ($res) {$result[]=array("id"=>$r["id"],"text"=>$data,"item"=>$r);}
        }
        if ($single AND count($result)>0) {$result=$result[0];}
        header('Content-Type: application/json');
        return wbJsonEncode($result);
    }
}

function form__controller__setup_engine() {
	if (isset($_POST["setup"]) AND $_POST["setup"]=="done" AND !is_dir($_ENV["path_app"]."/form")) {
		unset($_ENV["DOM"],$_ENV["errors"]);
		wbRecurseCopy($_ENV["path_engine"]."/_setup/",$_ENV["path_app"]);
		wbTableCreate("pages");
		wbTableCreate("users");
		wbTableCreate("todo");
		wbTableCreate("news");
		wbTableCreate("orders");
		$user=array("id"=>$_POST["login"],"password"=>md5($_POST["password"]),"role"=>"admin","point"=>"/admin/","active"=>"on","super"=>"on");
		wbItemSave("users",$user);

		header('Location: '.'/');
		die;
	}
	//if (is_dir($_ENV["dba"])) {die("Установка уже выполнена");}

		echo wbGetTpl("setup.htm");


	die;
}

?>
