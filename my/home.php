<?php
require_once(__DIR__ . '/../config.php');
require_once($CFG->dirroot . '/my/lib.php');

redirect_if_major_upgrade_required();

require_login();

$hassiteconfig = has_capability('moodle/site:config', context_system::instance());
if ($hassiteconfig && moodle_needs_upgrading()) {
    redirect(new moodle_url('/admin/index.php'));
}

$context = context_system::instance();

// Get the My Moodle page info.  Should always return something unless the database is broken.
if (!$currentpage = my_get_page(null, MY_PAGE_PUBLIC, MY_PAGE_COURSES)) {
    throw new Exception('mymoodlesetup');
}

// Start setting up the page.
$PAGE->set_context($context);
$PAGE->set_url('/my/home.php');
$PAGE->add_body_classes(['limitedwidth', 'page-myhome']);
$PAGE->set_pagelayout('myhome');
$PAGE->set_title('MyHome');


// No blocks can be edited on this page (including by managers/admins) because:
// - Course overview is a fixed item on the page and cannot be moved/removed.
// - We do not want new blocks on the page.
// - Only global blocks (if any) should be visible on the site panel, and cannot be moved int othe centre pane.
$PAGE->force_lock_all_blocks();

// Force the add block out of the default area.
$PAGE->theme->addblockposition  = BLOCK_ADDBLOCK_POSITION_CUSTOM;

// Add course management if the user has the capabilities for it.
$coursecat = core_course_category::user_top();
$coursemanagemenu = [];
if ($coursecat && ($category = core_course_category::get_nearest_editable_subcategory($coursecat, ['create']))) {
    // The user has the capability to create course.
    $coursemanagemenu['newcourseurl'] = new moodle_url('/course/edit.php', ['category' => $category->id]);
}
if ($coursecat && ($category = core_course_category::get_nearest_editable_subcategory($coursecat, ['manage']))) {
    // The user has the capability to manage the course category.
    $coursemanagemenu['manageurl'] = new moodle_url('/course/management.php', ['categoryid' => $category->id]);
}
if (!empty($coursemanagemenu)) {
    // Render the course management menu.
    $PAGE->add_header_action($OUTPUT->render_from_template('my/dropdown', $coursemanagemenu));
}

echo $OUTPUT->header();

if (core_userfeedback::should_display_reminder()) {
    core_userfeedback::print_reminder_block();
}
global $USER , $DB;
 $sqlusers = "select cu.companyid,cu.userid, u.firstname,u.lastname,u.deleted,cu.managertype,cu.educator from 
		mdl_company_users cu inner join mdl_user u on u.id=cu.userid
		where cu.userid= '".$USER->id."' AND u.deleted=0  " ;
	 $resusers = $DB->get_record_sql($sqlusers); 
	$companyid = $resusers->companyid;
	$company = $DB->get_record('company', array('id' => $companyid)); 
	$maincolor = $company->headingcolor;
//echo $OUTPUT->custom_block_region('content');
?>
<script>
// Get the elements by their class name
/* var myElements = document.getElementsByClassName("a");

// Loop through the elements
for (var i = 0; i < myElements.length; i++) {
  // Get the current background color of the element
  var currentColor = myElements[i].style.backgroundColor;

  // Print the current background color to the console
  console.log("The current background color is: " + currentColor);

  // Change the background color of the element to blue
  //myElements[i].style.backgroundColor = "blue";

  // Print the new background color to the console
  console.log("The new background color is: " + myElements[i].style.backgroundColor);
} */

</script>

<center>
    <h1 style="color:<?php echo $maincolor;?>;font-size: 50px;"><b><?php echo $company->name; ?></b></h1>
    <!--SCHOOL TITLE-->
    <br><br>

    <div style="width:100%; height: 100%;">
        <div class="row "><br><br>
            <div style="text-align:center;width:100%;background-repeat:no-repeat;background-size: cover; ">
                <p style="font-size: 50px; color:#ffffff;"><b><br></b></p>
                <p style="font-size: 50px; color:#ffffff;"><b>Virtual Curriculum</b></p>
                <p style="text-align: center;"><br></p>
                <p style="text-align: center;"><br></p>
                <br><br><br>
            </div>
        </div>
    </div>


    <div class="d-grid gap-2 d-md-block" style="padding-bottom:3%; padding-top: 5%;">
        <a href="/my" style="background-color:<?php echo $maincolor;?>;color:#ffffff;width:250px;margin-right:40px;" class="btn btn-primary"><span style="color:#ffffff;font-size:18px;">Dashboard</span></a>
        <a href="/my/courses.php" style="background-color:<?php echo $maincolor;?>;color:#ffffff;width:250px;" class="btn btn-primary"><span style="color:#ffffff;font-size:18px;">My Courses</span></a>
    </div>


    <center>
        <div style="width:100%; height: 100%; ">
            <div class="row "><br><br>
                <div style="background-color: #1c6985; width: 100%; height: 100%; border: solid; border-color: #1c6985; padding: 10px;">
                    <div style="text-align:center;width:95%;background-color:<?php echo $maincolor;?>;background-size: cover; ">
                        <p style="font-size: 50px; color:#ffffff;"><b><br></b></p>
                        <p style="font-size: 50px; color:#ffffff;"><b>ANNOUNCEMENTS</b></p><br>
                        <p style="font-size: 30px; color:#ffffff;">Turn your assignments in before July 27th</p>
                        <p style="font-size: 30px; color:#ffffff;">NEWS!!&nbsp;</p>
                        <p style="font-size: 30px; color:#ffffff;">-- AUGUST 11TH --&nbsp;</p>
                        <p style="font-size: 30px; color:#ffffff;">-- AUGUST 12TH --&nbsp;</p>
                        <p style="font-size: 30px; color:#ffffff;"><br></p>
                        <p style="text-align: center;"></p>
                        <p style="text-align: center;"><br></p>

                        <br>
                    </div>
                </div>
            </div>
        </div>
    </center>
</center>


<?php

echo $OUTPUT->footer();

// Trigger dashboard has been viewed event.
$eventparams = array('context' => $context);
$event = \core\event\mycourses_viewed::create($eventparams);
$event->trigger();
