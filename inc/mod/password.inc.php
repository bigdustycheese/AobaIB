<?php

if (!defined("IN_MOD")) {

    die("Nah, I won't serve that file to you.");

}

$mitsuba->admin->reqPermission("user.change_password");

if ((!empty($_POST['old'])) && (!empty($_POST['new'])) && (!empty($_POST['new2']))) {

    $mitsuba->admin->ui->checkToken($_POST['token']);

    if ($_POST['new']==$_POST['new2']) {



        $result = $conn->query("SELECT password,salt FROM users WHERE id=".$_SESSION['id']);

        $row = $result->fetch_assoc();

        if ($row['password'] != hash("sha512", $_POST['old'].$row['salt'])) {

            ?>

    <?php $mitsuba->admin->ui->startSection($lang['mod/pwd_no_match']); ?>

    <a href="?/password"><?php echo $lang['mod/back']; ?></a><?php $mitsuba->admin->ui->endSection(); ?>

        <?php

        } else {

            $conn->query("UPDATE users SET password='".hash("sha512", $_POST['new'].$row['salt'])."' WHERE id=".$_SESSION['id']);

                ?>

            <?php $mitsuba->admin->ui->startSection($lang['mod/pwd_updated']); ?>

            <a href="?/password"><?php echo $lang['mod/back']; ?></a><?php $mitsuba->admin->ui->endSection(); ?>

                <?php

        }

    } else {

        ?>

    <?php $mitsuba->admin->ui->startSection($lang['mod/pwd_wrong']); ?>

 <a href="?/password"><?php echo $lang['mod/back']; ?></a><?php $mitsuba->admin->ui->endSection(); ?>

    <?php

    }

} else {

    ?>

<?php $mitsuba->admin->ui->startSection(""); ?>
<style>
    meter {
    /* Reset the default appearance */
    -webkit-appearance: none;
         -moz-appearance: none;
   appearance: none;

    margin: 0 auto 1em;
    width: 100%;
    height: 0.5em;

    /* Applicable only to Firefox */
    background: none;
    background-color: rgba(0, 0, 0, 0.1);
    }

    meter::-webkit-meter-bar {
    background: none;
    background-color: rgba(0, 0, 0, 0.1);
    }

    /* Webkit based browsers */
    meter[value="1"]::-webkit-meter-optimum-value { background: red; }
    meter[value="2"]::-webkit-meter-optimum-value { background: yellow; }
    meter[value="3"]::-webkit-meter-optimum-value { background: orange; }
    meter[value="4"]::-webkit-meter-optimum-value { background: green; }

    /* Gecko based browsers */
    meter[value="1"]::-moz-meter-bar { background: red; }
    meter[value="2"]::-moz-meter-bar { background: yellow; }
    meter[value="3"]::-moz-meter-bar { background: orange; }
    meter[value="4"]::-moz-meter-bar { background: green; }
</style>
<div class="col-md-6">

<div class="box box-primary">

              <div class="box-header">

                <h3 class="box-title"><?php echo $lang['mod/pwd_change'];?></h3>

              </div><!-- /.box-header -->

              <!-- form start -->

              <form action="?/password" method="POST">

            <?php $mitsuba->admin->ui->getToken($path); ?>

                <div class="box-body">

                  <div class="form-group">

                    <label for="exampleInputEmail1"><?php echo $lang['mod/pwd_current']; ?></label>

                    <input type="password" class="form-control" name="old">

                  </div>

                  <div class="form-group">

                    <label for="exampleInputPassword1"><?php echo $lang['mod/pwd_new']; ?></label>

                    <input type="password" class="form-control" name="new" id="password">
         <meter max="4" id="password-strength-meter"></meter>
         <p id="password-strength-text"></p>
                  </div>

                  <div class="form-group">

                    <label for="exampleInputPassword1"><?php echo $lang['mod/pwd_confirm']; ?></label>

                    <input type="password" class="form-control" name="new2">

                  </div>
        <div class="form-group">

        </div>
                </div><!-- /.box-body -->



                <div class="box-footer">

                  <button type="submit" class="btn btn-primary">Submit</button>

                </div>

              </form>

            </div>

</div>
<script src="/js/zxcvbn.js"></script>
<script type="text/javascript">
var strength = {
        0: "Worst",
        1: "Bad",
        2: "Weak",
        3: "Good",
        4: "Strong"
}

var password = document.getElementById('password');
var meter = document.getElementById('password-strength-meter');
var text = document.getElementById('password-strength-text');

password.addEventListener('input', function()
{
    var val = password.value;
    var result = zxcvbn(val);

    // Update the password strength meter
    meter.value = result.score;

    // Update the text indicator
    if(val !== "") {
      text.innerHTML = "Strength: " + "<strong>" + strength[result.score] + "</strong><br />" + "<span class='feedback'>" + result.feedback.warning + " " + result.feedback.suggestions + "</span";
    }
    else {
      text.innerHTML = "";
    }
});
</script>
<?php $mitsuba->admin->ui->endSection(); ?>

    <?php

}

?>
