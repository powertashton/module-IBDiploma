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
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program. If not, see <http://www.gnu.org/licenses/>.
*/

@session_start();

//Module includes
include './modules/'.$_SESSION[$guid]['module'].'/moduleFunctions.php';

if (isModuleAccessible($guid, $connection2) == false) {
    //Acess denied
    $page->addError(__('You do not have access to this action.'));
} else {
    $page->breadcrumbs
        ->add(__('Welcome'));
    echo '<p>';
    echo 'Hover over any of the IB Learner Profile keywords for a quick reminder of their meaning in relation to your studies as an IB student: ';
    echo '</p>';
    echo "<table class='smallIntBorder' cellspacing='0' style='width: 100%'>";
    echo '<tr>';
    echo '<td>';
    echo "<ul style='margin-left: 10px'>";
    echo "<li><span title='They develop their natural curiosity. They acquire the skills necessary to conduct inquiry and research and show independence in learning. They actively enjoy learning and this love of learning will be sustained throughout their lives'>Inquirers</span></li>";
    echo "<li><span title='They explore concepts, ideas and issues that have local and global significance. In so doing, they acquire in-depth knowledge and develop understanding across a broad and balanced range of disciplines'>Knowledgeable</span></li>";
    echo '</ul>';
    echo '</td>';
    echo '<td>';
    echo "<ul style='margin-left: 10px'>";
    echo "<li><span title='They exercise initiative in applying thinking skills critically and creatively to recognize and approach complex problems, and make reasoned, ethical decisions'>Thinkers</span></li>";
    echo "<li><span title='They understand and express ideas and information confidently and creatively in more than one language and in a variety of modes of communication. They work effectively and willingly in collaboration with others'>Communicators</span></li>";
    echo '</ul>';
    echo '</td>';
    echo '<td>';
    echo "<ul style='margin-left: 10px'>";
    echo "<li><span title='They act with integrity and honesty, with a strong sense of fairness, justice and respect for the dignity of the individual, groups and communities. They take responsibility for their own actions and the consequences that accompany them'>Principled</span></li>";
    echo "<li><span title='They understand and appreciate their own cultures and personal histories, and are open to the perspectives, values and traditions of other individuals and communities. They are accustomed to seeking and evaluating a range of points of view, and are willing to grow from the experience'>Open-minded</span></li>";
    echo '</ul>';
    echo '</td>';
    echo '<td>';
    echo "<ul style='margin-left: 10px'>";
    echo "<li><span title='They show empathy, compassion and respect towards the needs and feelings of others. They have a personal commitment to service, and act to make a positive difference to the lives of others and to the environment'>Caring</span></li>";
    echo "<li><span title='They approach unfamiliar situations and uncertainty with courage and forethought, and have the independence of spirit to explore new roles, ideas and strategies. They are brave and articulate in defending their beliefs;'>Risk-takers</span></li>";
    echo '</ul>';
    echo '</td>';
    echo '<td>';
    echo "<ul style='margin-left: 10px'>";
    echo "<li><span title='They understand the importance of intellectual, physical and emotional balance to achieve personal well-being for themselves and others'>Balanced</span></li>";
    echo "<li><span title='They give thoughtful consideration to their own learning and experience. They are able to assess and understand their strengths and limitations in order to support their learning and personal development'>Reflective</span</li>";
    echo '</ul>';
    echo '</td>';
    echo '</tr>';
    echo '</table>';

    echo '<h2>';
    echo 'IB Diploma Structure';
    echo '</h1>';
    echo '<p>';
    echo "<img title='IB Diploma Structure Chart' src='".$_SESSION[$guid]['absoluteURL']."/modules/IB Diploma/img/IBDiplomaChart.png'><br/>";
    echo '</p>';
}
