$project = pods('project', get_the_ID());
$project->find();

$aProject = array(
'name' => $project->field('name'),
'url' => get_permalink($project->ID())