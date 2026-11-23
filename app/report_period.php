<?php
/** Works out the reporting period from ?range=7|30|90 or ?from=&to= (shared by the report page and CSV). */
function report_period(): array
{
    $range = get_str('range', '30');
    $from = get_str('from');
    $to = get_str('to');
    if ($range === 'custom' && valid_date($from) && valid_date($to)) {
        if ($from > $to) [$from, $to] = [$to, $from];
        if ($to > date('Y-m-d')) $to = date('Y-m-d');
        if ((strtotime($to) - strtotime($from)) / 86400 > 366) $from = date('Y-m-d', strtotime($to . ' -366 days'));
        return ['custom', $from, $to];
    }
    $days = in_array($range, ['7', '30', '90'], true) ? (int)$range : 30;
    return [(string)$days, date('Y-m-d', strtotime('-' . ($days - 1) . ' days')), date('Y-m-d')];
}
