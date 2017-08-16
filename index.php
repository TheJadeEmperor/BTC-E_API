<?php
session_start();


if($_POST['login']) {
    if($_POST['username'] == 'SetoKaiba' && $_POST['password'] == 'password') {
        $_SESSION['admin'] = $_POST['username'];
        header('Location: main.php');
}
    else {
        $err = '<p><font color="red"><b>Wrong credentials</b></font></p>';
    }
}

$bootDir = $dir.'include/bootstrap/';
$dir = 'include/'
?>
<head>
	<title>BTC API Dashboard</title>

	<link href="<?=$dir?>admin.css" rel="stylesheet" type="text/css"/>	
	<link href="<?= $bootDir ?>css/bootstrap-theme.css" rel="stylesheet" />
	<link href="<?= $bootDir ?>css/bootstrap.css" rel="stylesheet" />

	<style>
		.loginBox {
			width: 400px;
		}
	</style>
</head>
<center>
<p>&nbsp;</p>
<p>Admin Login</p>

<?=$err ?>
<form method=POST>
    <div class="loginBox">
    <div class="panel panel-primary">
        <div class="panel-heading"><h2 class="panel-title">Control Panel</h2></div>
        <div class="panel-body">
            <table>
                <tr>
                    <td width="90px"><p>Username</p> </td>
                    <td>
                        <p><input type="text" class="activeField" size="26" name="username" /></p>
                    </td>
                </tr>
                <tr>
                    <td><p>Password</p> </td>
                    <td>
                        <p><input type="password" class="activeField" size="26" name="password" /></p>
                    </td>
                </tr>
                <tr>
                    <td colspan="2" align="center">
                        <p><input type="submit" name="login" value=" Login to Admin Panel " class="btn info"></p>
                    </td>
                </tr>
            </table>
        </div>
    </div>    
    </div>
</form>

<a href="../">Return to website</a>
</center>