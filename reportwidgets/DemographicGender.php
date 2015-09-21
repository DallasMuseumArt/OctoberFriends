<?php

namespace DMA\Friends\ReportWidgets;

use Backend\Classes\ReportWidgetBase;
use Rainlab\User\Models\User;
use DMA\Friends\Models\Usermeta;
use DB;
use Lang;

class DemographicGender extends ReportWidgetBase
{
    public function render()
    {
        $results = Usermeta::select(DB::raw('count(user_id) as count'), 'gender')
            ->groupBy('gender')
            ->get();

        $count = User::count();
        $total = 0;

        foreach($results as $result) {
            if (empty($result->gender)) continue;

            $data[$result->gender] = $result->count;
            $total += $result->count;
        }

        $data[Lang::get('dma.friends::lang.user.noGender')] = $count - $total;

        arsort($data);

        $this->vars['data'] = $data;

        return $this->makePartial('widget');
    }
}
