<? if ($stats['ChallengesCompleted'] != false): ?>
    <div class="span5">
        <strong>Challenges Completed</strong><br />
        <div class="well">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th></th>
                        <th>Gamertag</th>
                        <th>Challenges</th>
                    </tr>
                </thead>
                <tbody>
                    <? $x = 1; foreach ($stats['ChallengesCompleted'] as $item): ?>
                        <tr>
                            <td><?= $this->library->get_trophy($x); ?></td>
                            <td><a href="<?= base_url('gt/' . str_replace(" ", "_", $item['Gamertag'])); ?>"><?= $item['Gamertag']; ?></a></td>
                            <td><?= number_format($item['TotalChallengesCompleted']); ?></td>
                        </tr>
                    <? $x++; endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
<? endif; ?>