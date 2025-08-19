<?php

namespace App\Exports;

use App\Models\TagsModel;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Carbon\Carbon;


class TagsExport implements FromCollection, WithHeadings
{
    protected $type;
    protected $month;
    protected $year;
    protected $startDate;
    protected $endDate;

    public function __construct($params)
    {
        $this->type = $params['type'] ?? null;
        $this->month = $params['month'] ?? null;
        $this->year = $params['year'] ?? null;
        $this->startDate = $params['start_date'] ?? null;
        $this->endDate = $params['end_date'] ?? null;
    }

    public function collection()
    {
        $query = TagsModel::query();
        

        if ($this->type === 'month' && $this->month && $this->year) {
            $query->whereYear('created_at', $this->year)
                  ->whereMonth('created_at', $this->month);
        } elseif ($this->type === 'date' && $this->startDate && $this->endDate) {
            $query->whereBetween('created_at', [
                Carbon::parse($this->startDate)->startOfDay(),
                Carbon::parse($this->endDate)->endOfDay()
            ]);
        } elseif ($this->type === 'year' && $this->year) {
            $query->whereYear('created_at', $this->year);
        }

        return $query->get(['id', 'name', 'slug', 'created_at']);
    }

    public function headings(): array
    {
        return [
            'id',
            'Nama Tag',
            'Slug',
            'Tanggal Dibuat',
        ];
    }
}
