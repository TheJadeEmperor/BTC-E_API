<?php
include('Bitfinex.php');
//include('config.php');
 define('API_KEY','lhB8h0aZSH8v49CbIM6Ib3bee6tHQ8aZZbmbTjf2OYM');
 define('API_SECRET','VHcTwmMwEoy2jBhGvMcldBZ5dy9OjosKuynmut05dcw');
$call = new Bitfinex(API_KEY,API_SECRET);


if(isset($_REQUEST['show_active_order'])){
  $return_data = $call->active_orders();
}

if(isset($_REQUEST['show_active_positions'])){
  $return_data = $call->active_positions();
}

if(isset($_REQUEST['show_acc_information'])){
  $return_data = $call->acc_info();
}

if(isset($_POST['submit']))
{
	$data['symbol']  =   $_POST['symbol'];
	$data['amount']  =   $_POST['amount'];
	$data['price']   =   $_POST['price'];
	$data['exchange']  = 'bitfinex';  //$_POST['exchange'];
	$data['side']    =   $_POST['side'];
	$data['type']    =   $_POST['type'];

  $msg = $call->new_order($data);
}

?>
<html>
<head>
	<title>New Order</title>
	<!-- Latest compiled and minified CSS -->
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" >
</head>
<body>
<div id="login-overlay" class="modal-dialog">
      <div class="modal-content">
          <div class="modal-header">
              <h4 class="modal-title" id="myModalLabel">New Order<b></b></h4>
          </div>
          <div class="modal-body">
              <div class="row">
                  <div class="col-xs-12">
                      <div class="well">                        
                          <form id="loginForm" method="POST">
                              <div class="form-group">
                                  <label for="username" class="control-label">Symbol</label>
                                  <input type="text" class="form-control" name="symbol" value=""   placeholder="ex. BTCUSD" required>
                                  <span class="help-block"></span>
                              </div>
                               <div class="form-group">
                                  <label for="username" class="control-label">Amount</label>
                                  <input type="text" class="form-control" name="amount" value=""   placeholder="ex. 0.01" required>
                                  <span class="help-block"></span>
                              </div>
                               <div class="form-group">
                                  <label for="username" class="control-label">Price</label>
                                  <input type="text" class="form-control" name="price" value=""   placeholder="ex. 0.01" required>
                                  <span class="help-block"></span>
                              </div>

                              <!--<div class="form-group">
                                  <label for="username" class="control-label">Exchange</label>
                                  <input type="text" class="form-control" name="exchange" value=""   placeholder="ex. bitfinex" required>
                                  <span class="help-block"></span>
                              </div>-->

                               <div class="form-group">
                                  <label for="username" class="control-label">Side</label>
                                  <input type="text" class="form-control" name="side" value=""   placeholder="ex. buy or sell" required>
                                  <span class="help-block"></span>
                              </div>

                               <div class="form-group">
                                  <label for="username" class="control-label">type</label>
                                  <input type="text" class="form-control" name="type" value=""   placeholder="ex. exchange limit" required>
                                  <span class="help-block"></span>
                              </div>
                             
                              <button type="submit" value="Submit" name="submit" class="btn btn-success btn-block">Submit</button>
                              <a href="<?php echo $_SERVER['PHP_SELF'].'?show_active_order=1' ; ?>" class="btn btn-success btn-block">See active orders</a>
                              <a href="<?php echo $_SERVER['PHP_SELF'].'?show_active_positions=1' ; ?>" class="btn btn-success btn-block">See active positions</a>
                              <a href="<?php echo $_SERVER['PHP_SELF'].'?show_acc_information=1' ; ?>" class="btn btn-success btn-block">See account information</a>
                              
                          </form>

                           <?php
                             if(isset($msg)){
                              echo 'Result : <h3>'.$msg['message'].'</h3>';
                             }

                             if(isset($return_data)){
                              if(count($return_data)){
                                echo 'Result : <pre>'; print_r($return_data); echo '</pre>';
                              }else{
                                echo 'No record found'; 
                              }
                             } 
                           ?>

                      </div>
                  </div>
                  
              </div>
          </div>
      </div>
      <?php 

        if(isset($_POST['claimposition'])){ 
              $data['position_id']  =   (int)$_POST['position_id'];       
              $position_msg = $call->claim_positions($data);            
        }
      ?>
      <div class="modal-content">
          <div class="modal-header">
              <h4 class="modal-title" id="myModalLabel">Claim Position<b></b></h4>
          </div>
          <div class="modal-body">
              <div class="row">
                  <div class="col-xs-12">
                      <div class="well">
                        <?php if(isset($position_msg)){
                          echo '<h3>'.$position_msg['message'].'</h3>';
                        }?>
                          <form id="loginForm2" method="POST">
                              <div class="form-group">
                                  <label for="username" class="control-label">Position Id</label>
                                  <input type="text" class="form-control" name="position_id" value=""   placeholder="ex. 943737" required>
                                  <span class="help-block"></span>
                              </div>
                                
                             
                              <button type="submit" value="Submit" name="claimposition" class="btn btn-success btn-block">Submit</button>
                              
                          </form> 
                      </div>
                  </div>
                  
              </div>
          </div>
      </div>

  </div>

</body>
</html>
