<? if ($stats['Data']['medals_per_game'] != false): ?>
    <table class="table table-bordered table-striped">
        <thead>
        <tr>
            <th></th>
            <th>Gamertag</th>
            <th>Medals per Game</th>
        </tr>
        </thead>
        <tbody>
        <? $x = 1; foreach ($stats['Data']['medals_per_game'] as $item): ?>
            <tr>
                <td><?= $this->h4_lib->get_trophy($x); ?></td>
                <td><a href="<?= base_url('h4/record/' . $item[H4::SEO_GAMERTAG]); ?>">
                        <?= $item[H4::GAMERTAG]; ?>
                        <? if (isset($item[H4::BADGE])): ?>
                            - <span class="badge badge-<?= $item[H4::BADGE_COLOR]; ?>"><?= $item[H4::BADGE]; ?></span>
                        <? endif; ?>
                    </a></td>
                <td><?= number_format($item[H4::MEDALS_PER_GAME_RATIO],2); ?></td>
            </tr>
            <? $x++; endforeach; ?>
        </tbody>
    </table>
<? endif; ?>