<?php
// Start the session
session_start();

// Check if the user is not logged in
if (!isset($_SESSION['user_id'])) {
    // Redirect to the login page or any other desired page if not logged in
    header("Location: login.html");
    exit();
}
?>



<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-rbsA2VBKQhggwzxH7pPCaAqO46MgnOM80zW1RWuH61DGLwZJEdK2Kadq2F9CUG65" crossorigin="anonymous">
    <link rel="stylesheet" href="path/to/font-awesome/css/font-awesome.min.css">
    <link href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://netdna.bootstrapcdn.com/font-awesome/4.0.3/css/font-awesome.min.css">  
 <style>
body{
    margin-top:20px;
    background:#95bbf0;
}
.order-card {
    color: #fff;
}

.bg-c-blue {
    background: linear-gradient(45deg,#4099ff,#73b4ff);
}

.bg-c-green {
    background: linear-gradient(45deg,#2ed8b6,#59e0c5);
}

.bg-c-yellow {
    background: linear-gradient(45deg,#FFB64D,#ffcb80);
}

.bg-c-pink {
    background: linear-gradient(45deg,#FF5370,#ff869a);
}


.card {
    border-radius: 5px;
    -webkit-box-shadow: 0 1px 2.94px 0.06px rgba(4,26,55,0.16);
    box-shadow: 0 1px 2.94px 0.06px rgba(4,26,55,0.16);
    border: none;
    margin-bottom: 30px;
    -webkit-transition: all 0.3s ease-in-out;
    transition: all 0.3s ease-in-out;
}

.card .card-block {
    padding: 25px;
}

.order-card i {
    font-size: 26px;
}

.f-left {
    float: left;
}

.f-right {
    float: right;
}


 </style>
  
  </head>
  <body >
  <h2 class="text-center" style="color:#293462;"><i class="fas fa-user-circle"></i>  ADMIN DASHBOARD</h2><br/>
     
  <link href="https://maxcdn.bootstrapcdn.com/font-awesome/4.3.0/css/font-awesome.min.css" rel="stylesheet">
<div class="container" style="color:#293462;border:black;border-width:2px; border-style:solid; ">
    <div class="row mt-5">
        <div class="col-md-4 col-xl-3">
            <div class="card bg-c-blue order-card"  style="background-color:#F65C78;">
                <a href="newpatient.php" style="color:black; text-decoration:none; "><div class="card-block">
                    
                    <h5 class="text-center"><i class="fa fa-user-plus f-center"></i>  <span> NEW PATIENTS</span></h5>
                    
                </div></a>
            </div>
        </div>
        
        <div class="col-md-4 col-xl-3 ">
            <div class="card bg-c-green order-card"  style="background-color:#F65C78;">
               <a href="ViewPatients.php" style="color:black; text-decoration:none; "> <div class="card-block">
                <h5 class="text-center"><i class="fa fa-users f-center"></i> <span> OLD PATIENTS</span></h5>
                </div></a>
            </div>
        </div>
        
        <div class="col-md-4 col-xl-3">
            <div class="card bg-c-yellow order-card"  style="background-color:#F65C78;">
            <a href="#" style="color:black; text-decoration:none; "> <div class="card-block">
                <h5 class="text-center"><i class="fa fa-id-card f-center"> </i> <span>REPORTS</span></h5>
                </div></a>
            </div>
        </div>
        <div class="col-md-4 col-xl-3">
            <div class="card order-card"  style="background-color:#D61C4E">
            <a href="logout.php" style="color:black; text-decoration:none; "> <div class="card-block">
                <h5 class="text-center"><i class="fa fa-sign-out" aria-hidden="true"></i><span>LOGOUT</span></h5>
                </div></a>
            </div>
        </div>
        
	</div>
    <div class="row mt-5">
        <div class="col-md-4 col-xl-3">
            <div class="card  order-card" style="background-color:#FE5DA1;">
                <a href="newpatient.php" style="color:black; text-decoration:none; "><div class="card-block">
                    
                    <h5 class="text-center"><i class="fa fa-envelope"></i> <span> E-MAIL CAMPING </span></h5>
                    
                </div></a>
            </div>
        </div>
        
        <div class="col-md-4 col-xl-3 ">
            <div class="card order-card"  style="background-color:#F47121;">
               <a href="viewphoto.php" style="color:black; text-decoration:none; "> <div class="card-block">
                <h5 class="text-center"><i class="fa-solid fa-image"></i> <span>  PATIENTS PHOTOS</span></h5>
                </div></a>
            </div>
        </div>
        
        <div class="col-md-4 col-xl-3">
            <div class="card  order-card" style="background-color:#F65C78;">
            <a href="#" style="color:black; text-decoration:none; "> <div class="card-block">
                <h5 class="text-center"><i class="fa-solid fa-disease"></i><span>ADD SYMPTOMES</span></h5>
                </div></a>
            </div>
        </div>
        <div class="col-md-4 col-xl-3 " >
            <div class="card order-card"   style="background-color:#FEC5E6;">
            <a href="logout.php" style="color:black; text-decoration:none; "> <div class="card-block">
                <h5 class="text-center"><i class="fa fa-calendar" aria-hidden="true"></i> <span> NEXT VISITS </span></h5>
</div></a>
            </div>
        </div>
        
	</div>
</div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-kenU1KFdBIe4zVF0s0G1M5b4hcpxyD9F7jL+jjXkk+Q2h455rYXK/7HAuoJl+0I4" crossorigin="anonymous"></script>
  </body>
</html>


