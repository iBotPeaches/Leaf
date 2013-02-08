<h3>Challenges</h3>
<div class="well well-large">
    <? foreach ($challenges['Challenges'] as $challenge): ?>
        <strong><?= $challenge['Name']; ?></strong>
        <span class="badge badge-info"><?= $challenge['CategoryName']; ?></span> 
        <span class="badge badge-success">XP: <?= number_format($challenge['XpReward']); ?></span><br />
        <i><?= $challenge['Description']; ?></i><br />
        <small>Time left: <?= $this->library->time_duration($challenge['EndDate'] - time()); ?></small>
        <br /><br />
    <? endforeach; ?>
</div>