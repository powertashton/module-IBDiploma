<?php
/*
Gibbon, Flexible & Open School System
Copyright (C) 2010, Ross Parker

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

@session_start();

//Module includes
include './modules/'.$_SESSION[$guid]['module'].'/moduleFunctions.php';

if (isActionAccessible($guid, $connection2, '/modules/IB Diploma/cas_student_reflections_delete.php') == false) {
    //Acess denied
    echo "<div class='error'>";
    echo 'You do not have access to this action.';
    echo '</div>';
} else {
    if (enroled($guid, $_SESSION[$guid]['gibbonPersonID'], $connection2) == false) {
        //Acess denied
        echo "<div class='error'>";
        echo 'You are not enroled in the IB Diploma programme.';
        echo '</div>';
    } else {
        //Proceed!
        echo "<div class='trail'>";
        echo "<div class='trailHead'><a href='".$_SESSION[$guid]['absoluteURL']."'>Home</a> > <a href='".$_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_GET['q']).'/'.getModuleEntry($_GET['q'], $connection2, $guid)."'>".getModuleName($_GET['q'])."</a> > <a href='".$_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_GET['q'])."/cas_student_reflections.php'>Reflections</a> > </div><div class='trailEnd'>Delete Reflection</div>";
        echo '</div>';

        if (isset($_GET['return'])) {
            returnProcess($guid, $_GET['return'], null, null);
        }

        //Check if school year specified
        $ibDiplomaCASReflectionID = $_GET['ibDiplomaCASReflectionID'];
        if ($ibDiplomaCASReflectionID == '') {
            echo "<div class='error'>";
            echo 'You have not specified a reflection.';
            echo '</div>';
        } else {
            try {
                $data = array('gibbonPersonID' => $_SESSION[$guid]['gibbonPersonID'], 'ibDiplomaCASReflectionID' => $ibDiplomaCASReflectionID);
                $sql = 'SELECT * FROM ibDiplomaCASReflection WHERE gibbonPersonID=:gibbonPersonID AND ibDiplomaCASReflectionID=:ibDiplomaCASReflectionID';
                $result = $connection2->prepare($sql);
                $result->execute($data);
            } catch (PDOException $e) {
                echo "<div class='error'>".$e->getMessage().'</div>';
            }

            if ($result->rowCount() != 1) {
                echo "<div class='error'>";
                echo 'The selected reflection does not exist.';
                echo '</div>';
            } else {
                //Let's go!
                $row = $result->fetch();
                ?>
				<form method="post" action="<?php echo $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module']."/cas_student_reflections_deleteProcess.php?ibDiplomaCASReflectionID=$ibDiplomaCASReflectionID" ?>">
					<table class='smallIntBorder' cellspacing='0' style="width: 100%">
						<tr>
							<td>
								<b>Are you sure you want to delete this reflection?</b><br/>
								<span style="font-size: 90%; color: #cc0000"><i>This operation cannot be undone, and may lead to loss of vital data in your system.<br/>PROCEED WITH CAUTION!</i></span>
							</td>
							<td class="right">

							</td>
						</tr>
						<tr>
							<td>
								<input name="ibDiplomaCASReflectionID" id="ibDiplomaCASReflectionID" value="<?php echo $ibDiplomaCASReflectionID ?>" type="hidden">
								<input type="hidden" name="address" value="<?php echo $_SESSION[$guid]['address'] ?>">
								<input type="submit" value="Yes">
							</td>
							<td class="right">

							</td>
						</tr>
					</table>
				</form>
				<?php

            }
        }
    }
}
?>
