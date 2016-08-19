<!DOCTYPE html>
<html>
    <head>
    <script src="dist/js/jquery.min.1.12.js"></script>
    <script type="text/javascript" src="dist/js/bootstrap.min.js"></script>    
    <link rel="stylesheet" type="text/css" href="dist/css/bootstrap.min.css" />
    </head>
    <body>      
<?php
    // Include Cex.io PHP API
    require_once "cexapi.php"; 

    // Create API Object
    // $obj = new cexApi("User id", "Key", "Secret");

    $api = new cexApi("up100154867", "FihnsedBXmpLeE4wo4GqjdE6pcI", "VkuVEuKvzeqdg7JR30nl0yvXSY");    // change this user details to your account details
    

    ?>
<div class="container">
    <div class="panel panel-primary">
        <div class="panel-heading">
            <h3>Cex API</h3></div>
        <div class="panel-body">
            <div class="row">
 
<div class="col-md-12">
<!-- Nav tabs -->
<div class="card">
<ul class="nav nav-tabs" role="tablist">
    <li role="presentation" class="active"><a href="#pOrder" aria-controls="pOrder" role="tab" data-toggle="tab">Place order</a></li>
    <li role="presentation"><a href="#cOrder" aria-controls="cOrder" role="tab" data-toggle="tab">Cancel order</a></li>
    <li role="presentation"><a href="#balance" aria-controls="balance" role="tab" data-toggle="tab">Get balance</a></li>
    
</ul>

    <!-- Tab panes -->
    <div class="tab-content">
        <div role="tabpanel" class="tab-pane active" id="pOrder">
        	<?php // Place order 
                echo "<pre>", json_encode($api->place_order()), "</pre>";?>
        </div>
        <div role="tabpanel" class="tab-pane" id="cOrder">                  
        <?php // Cancel order

                echo "<pre>", json_encode($api->cancel_order()), "</pre>"; ?>
        </div>
        <div role="tabpanel" class="tab-pane" id="balance">
        	<?php // Get account balance 
                echo "<pre>", json_encode($api->balance()), "</pre>"; ?>
        </div>
        
    </div>
</div>
</div>
    </div>


        </div>
    </div>
	
</div>
</body>
<style type="text/css">
    
    .nav-tabs { border-bottom: 2px solid #DDD; }
    .nav-tabs > li.active > a, .nav-tabs > li.active > a:focus, .nav-tabs > li.active > a:hover { border-width: 0; }
    .nav-tabs > li > a { border: none; color: #666; }
        .nav-tabs > li.active > a, .nav-tabs > li > a:hover { border: none; color: #4285F4 !important; background: transparent; }
        .nav-tabs > li > a::after { content: ""; background: #4285F4; height: 2px; position: absolute; width: 100%; left: 0px; bottom: -1px; transition: all 250ms ease 0s; transform: scale(0); }
    .nav-tabs > li.active > a::after, .nav-tabs > li:hover > a::after { transform: scale(1); }
.tab-nav > li > a::after { background: #21527d none repeat scroll 0% 0%; color: #fff; }
.tab-pane { padding: 15px 0; }
.tab-content{padding:20px}

.card {background: #FFF none repeat scroll 0% 0%; box-shadow: 0px 1px 3px rgba(0, 0, 0, 0.3); margin-bottom: 30px; }
body{ background: #EDECEC; padding:50px}
</style>