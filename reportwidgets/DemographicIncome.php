<?php

namespace DMA\Friends\ReportWidgets;

use Backend\Classes\ReportWidgetBase;
use Rainlab\User\Models\User;
use DMA\Friends\Models\Usermeta;
use DB;
use Lang;

class DemographicIncome extends ReportWidgetBase
{
    public function render()
    {
        $results = Usermeta::select(DB::raw('count(user_id) as count'), 'household_income')
            ->groupBy('household_income')
            ->get();

        $count = User::count();
        $total = 0;

        foreach($results as $result) {
            if (empty($result->household_income)) continue;

            $data[$result->household_income] = $result->count;
            $total += $result->count;
        }

        $data[Lang::get('dma.friends::lang.user.noIncome')] = $count - $total;

        arsort($data);

        $this->vars['data'] = $data;

        return $this->makePartial('widget');
    }
}
