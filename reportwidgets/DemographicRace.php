<?php

namespace DMA\Friends\ReportWidgets;

use Backend\Classes\ReportWidgetBase;
use Rainlab\User\Models\User;
use DMA\Friends\Models\Usermeta;
use DB;
use Lang;

class DemographicRace extends ReportWidgetBase
{
    public function render()
    {
        $results = Usermeta::select(DB::raw('count(user_id) as count'), 'race')
            ->groupBy('race')
            ->get();

        $count = User::count();
        $total = 0;

        foreach($results as $result) {
            if (empty($result->race)) continue;

            $data[$result->race] = $result->count;
            $total += $result->count;
        }

        $data[Lang::get('dma.friends::lang.user.noRace')] = $count - $total;

        arsort($data);

        $this->vars['data'] = $data;

        return $this->makePartial('widget');
    }
}
