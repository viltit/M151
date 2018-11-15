<?php
    $name = $firstName = $email = $username = $password = $pwConfirm = "";
    $errors = array();
    $message = "";

    // Do we have data in POST?
    if($_SERVER['REQUEST_METHOD'] == "POST") {

        // Name
        if(isset($_POST['name']) && !empty(trim($_POST['name'])) && strlen(trim($_POST['name'])) <= 20) {
            $name = htmlspecialchars(trim($_POST['name']));
        } 
        else {
            $errors[] = "Enter a valid name.";
        }
        // First name
        if(isset($_POST['firstName']) && !empty(trim($_POST['firstName'])) && strlen(trim($_POST['firstName'])) <= 20) {
            $firstName = htmlspecialchars(trim($_POST['firstName']));
        } 
        else {
            $errors[] = "Enter a valid first name.";
        }
        // Username
        if(isset($_POST['username']) && !empty(trim($_POST['username'])) && strlen(trim($_POST['username'])) <= 20) {
            $username = htmlspecialchars(trim($_POST['username']));
        } 
        else {
            $errors[] = "Enter a valid username.";
        }
        //email
        if(isset($_POST['email']) && !empty(trim($_POST['email'])) && strlen(trim($_POST['email'])) <= 40) {
            $email = htmlspecialchars(trim($_POST['email']));
            // check if adress seems valid
            if (filter_var($email, FILTER_VALIDATE_EMAIL) === false){
                $errors[] = "The given email adress seems to be invalid";
            }
        } else {
                $errors[] = "Enter a valid e-mail adress";
        }
        //password
        if(isset($_POST['password']) && !empty(trim($_POST['password']))) {
            $password = trim($_POST['password']);
        } 
        else {
            $errors[] = "Enter a valid password";
        }
        //confirm password
        if(isset($_POST['password2']) && !empty(trim($_POST['password2']))) {
            $pwConfirm = trim($_POST['password2']);
            if ($pwConfirm != $password) {
                $errors[] = "Your password and your confirmation password do not match.";
            }
        } 
        else {
            $errors[] = "Enter a valid password";
        }

        //no errors ? Look in the database if we already have a user with this username or this email:
        if(count($errors) == 0) {
            
            require("includes/dbConnect.php");
            //check if we already have a user with the same username or email
            $query1 = "SELECT * FROM Player WHERE username = ?";
            $query2 = "SELECT * FROM Player WHERE email = ?";

            $stm1 = $mysqli->prepare($query1);
            //TODO: Check if prepare returned false ??
            $stm1->bind_param("s", $username);
            $stm2 = $mysqli->prepare($query2);
            $stm2->bind_param("s", $email);

            $stm1->execute();
            $result = $stm1->get_result();
            if ($result->num_rows != 0) {
                $errors[] = "A user with you name already exists. Choose another username.";
            }
            $stm2->execute();
            $result = $stm2->get_result();
            if ($result->num_rows != 0) {
                $errors[] = "A user with your e-mail-adress already exist. Are you sure you are not registered yet?";
            }
            $stm1->close();
            $stm2->close();

            //and again, check for errors:
            if(count($errors) == 0) {
                $query = "INSERT INTO Player (name, firstName, email, username, password) VALUES (?,?,?,?,UNHEX(SHA1(?)))";
                $stm = $mysqli->prepare($query);
                $stm->bind_param("sssss", $name, $firstName, $email, $username, $password);
                if ($stm->execute()) {
                    $message = "Your registration was successful.";
                }
                else {
                    $errors[] = "We are sorry, but we seem to have a problem with our database. If the problem persists, please contact an admin.";
                }
                $stm->close();
            }
            $mysqli->close();
        }
    }
  
?>

<?php
    //Print error message or success message
    if(count($errors) > 0) {
        echo("<div class=\"alert alert-danger\" role=\"alert\">");
        foreach($errors as $error) {
            echo($error."<br>");
        }
        echo("</div>");
    } 
    else if (!empty($message)){
        echo "<div class=\"alert alert-success\" role=\"alert\">".$message."</div>";
    }
?>

<form action="" method="POST">
    <!-- Name -->
    <div class="form-group">
        <label for="name">Name *</label>
        <input type="text" name="name" minlength="3" maxlength="20" required class="form-control"
                value="<?php echo $name ?>"
                placeholder="3 to 20 characters">
    </div>
    <!-- First Name -->
    <div class="form-group">
        <label for="firstName">First name *</label>
        <input type="text" name="firstName" minlength="3" maxlength="20" required class="form-control"
                value="<?php echo $firstName ?>"
                placeholder="3 to 20 characters">
    </div>
    <!-- email -->
    <div class="form-group">
        <label for="email">E-Mail *</label>
        <input type="email" name="email" maxlength="40" required class="form-control"
                value="<?php echo $email ?>"
                placeholder="Enter your E-Mail-Adress here">
    </div>
    <!-- username -->
    <div class="form-group">
        <label for="username">Username *</label>
        <input type="text" name="username" minlength="3" maxlength="20" required class="form-control"
                placeholder="3 to 20 characters">
    </div>
    <!-- password -->
    <div class="form-group">
        <label for="password">Password *</label>
        <input type="password" name="password" minlength="6" maxlength="20" required class="form-control"
                placeholder="6 to 20 characters">
    </div>
    <!-- password confirm -->
    <div class="form-group">
        <label for="password2">Confirm Password *</label>
        <input type="password" name="password2" minlength="6" maxlength="20" required class="form-control"
                placeholder="6 to 20 characters">
    </div>

    <!-- Submit or reset -->
    <button type="submit" name="button" value="submit" class="btn btn-info">Submit</button>
    <button type="reset" name="button" value="reset" class="btn btn-warning">Reset</button>
</form>