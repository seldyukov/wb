<?php
function engine__controller() {
	wbTrigger("func",__FUNCTION__,"before");
	$call=__FUNCTION__ ."_".$_ENV["route"]["mode"];
	if (is_callable($call)) {
		$_ENV["DOM"]=$call();
	} else {
		echo __FUNCTION__ .": отсутствует функция ".$call."()";
		die;
	}
	wbTrigger("func",__FUNCTION__,"after");
	return $_ENV["DOM"];
}

function engine__controller_logout() {
	unset($_SESSION["user_id"],$_SESSION["user_role"],$_SESSION["user"]);
	header('Location: /login/');
}

function engine__controller_login() {
	//$user=array("id"=>"admin","password"=>md5("admin"),"role"=>"admin","point"=>"/admin/","active"=>"on");
	//wbItemSave("users",$user);
	if (isset($_POST["l"]) AND $_POST["l"]>"") {
		if ($user=wbItemRead("users",$_POST["l"])) {
				if ($user["password"]==md5($_POST["p"]) AND $user["active"]=="on") {
                    engine__controller_login_success($user);
				}
		}
	}
	$_ENV["DOM"]=wbGetTpl("login.htm");
	return $_ENV["DOM"];
}

function engine__controller_login_success($user) {
        $_SESSION["user_id"]=$user["id"];
        $_SESSION["user_role"]=$user["role"];
    if ($user["point"]>"") {$point=$user["point"];} else {$point="/";}
    if (!isset($user["name"])) {$user["name"]=$user["id"];}
        if (isset($user["avatar"])) {
            if (!is_array($user["avatar"])) {$user["avatar"]=json_decode($user["avatar"],true);$user["avatar"]=$user["avatar"][0];} 
            $user["avatar"]="/uploads/users/{$user["id"]}/{$user["avatar"]["img"]}";
        } else {$user["avatar"]="/engine/uploads/__system/person.svg";}
        $_SESSION["user"]=$user;
        header('Location: '.$point);
        die;  
}

function engine__controller_register() {
	$_ENV["DOM"]=wbGetTpl("register.htm");
	return $_ENV["DOM"];
}

function engine__controller_admin() {
	$_ENV["DOM"]=wbGetTpl("admin.htm");
	if (isset($_ENV["route"]["action"])) {
		$call=__FUNCTION__ ."_".$_ENV["route"]["action"];
		if (is_callable($call)) {
			$res=$call();
		} else {
			$res=__FUNCTION__ .": отсутствует функция ".$call."()";
		}
		echo $res; die;
	}
	return $_ENV["DOM"];
}

function engine__controller_admin_ui() {
	include_once (__DIR__."/admin_ui.php");
}

function engine__controller__logout() {
		$_SESSION["user"]=$_SESSION["user_role"]="";
		setcookie("user_id","",time()-3600,"/"); unset($_COOKIE["user_id"]);
		header("Refresh: 0; URL=http://{$_SERVER["HTTP_HOST"]}");
		echo "Выход из системы, ждите...";
		die;
}

function engine__controller_include() {
	include_once($_ENV["pathRoot"].$_SERVER["SCRIPT_NAME"]);
	die;
}

function engine__controller__ui() {
	echo 234;
}

?>
