<?php

namespace DMA\Friends\ReportWidgets;

use Backend\Classes\ReportWidgetBase;
use Rainlab\User\Models\User;
use DMA\Friends\Models\Usermeta;
use DB;
use Lang;

class DemographicEducation extends ReportWidgetBase
{
    public function render()
    {
        $results = Usermeta::select(DB::raw('count(user_id) as count'), 'education')
            ->groupBy('education')
            ->get();

        $count = User::count();
        $total = 0;

        foreach($results as $result) {
            if (empty($result->education)) continue;

            $data[$result->education] = $result->count;
            $total += $result->count;
        }

        $data[Lang::get('dma.friends::lang.user.noEducation')] = $count - $total;

        arsort($data);

        $this->vars['data'] = $data;

        return $this->makePartial('widget');
    }
}
