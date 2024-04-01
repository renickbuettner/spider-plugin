<div class="layout-row min-size">
    <div class="control-toolbar toolbar-padded">
            <div class="toolbar-item toolbar-primary">
                <div data-control="toolbar">
                    <button type="button"
                            data-request="onScheduleRun"
                            data-request-message="<?= trans("renick.spider::lang.overview.scheduling") ?>"
                            class="btn btn-primary oc-icon-spinner">Schedule run</button>
                </div>
            </div>

            <div class="toolbar-item">
                <div data-control="toolbar">
                    <div data-control="balloon-selector" class="control-balloon-selector">
                        <ul id="filter-selectors">
                            <li data-value="all"><?= trans("renick.spider::lang.overview.filters.all") ?></li>
                            <li data-value="errors"><?= trans("renick.spider::lang.overview.filters.errors") ?></li>
                        </ul>

                        <input type="hidden" name="balloonValue" value="1" />
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        const setupOverview = () => {
            const filterOptions = document.querySelectorAll('#filter-selectors li');
            filterOptions.forEach(function (option) {
                option.addEventListener('click', function () {
                    const value = this.getAttribute('data-value');
                    const location = new URL(window.location);

                    if (location.searchParams.get('filter') === value) {
                        return;
                    }

                    location.searchParams.set('filter', value);
                    window.location.href = location.href;
                });
            });

            const location = new URL(window.location);
            if (!location.searchParams.has('filter')) {
                location.searchParams.set('filter', 'all');
            }

            const value = location.searchParams.get('filter');
            const selectedOption = document.querySelector(`#filter-selectors li[data-value="${value}"]`);
            selectedOption.classList.add('active');
        }

        document.addEventListener('DOMContentLoaded', () => setTimeout(setupOverview, 600));
    </script>

    <div class="container">
        <h1 class="mb-4"><?= trans("renick.spider::lang.overview.title") ?></h1>
        <small class="d-block mb-4">
            <i class="icon-calendar"></i> <?= $date->isoFormat('lll') ?>
        </small>

        <?php if (empty($data)): ?>
            <p><?= trans("renick.spider::lang.overview.no_data") ?></p>
        <?php else: ?>
        <table class="table data">
            <thead>
            <tr>
                <th style="width: 150px"><span><?= trans("renick.spider::lang.overview.columns.status_code") ?></span></th>
                <th><span><?= trans("renick.spider::lang.overview.columns.url") ?></span></th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($data as $row): ?>
            <tr>
                <td>
                    <?php if ($row['status_code'] == 200): ?>
                        <span class="list-badge badge-info">
                            <i class="icon-info"></i>
                        </span>
                    <?php elseif ($row['status_code'] == 404): ?>
                        <span class="list-badge badge-danger">
                            <i class="icon-times"></i>
                        </span>
                    <?php else: ?>
                        <span class="list-badge badge-warning">
                            <i class="icon-exclamation"></i>
                        </span>
                    <?php endif; ?>

                    <?= $row['status_code'] ?? '-' ?>
                </td>
                <td>
                    <a href="<?= $row['url'] ?? '-' ?>" target="_blank">
                        <?= $row['url'] ?? '-' ?>
                    </a>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>
</div>
