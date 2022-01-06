<?php

namespace App\Services;

use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Str;
use App\Models\Sky\Year;
use App\Models\Sky\View as Views;
use App\Models\Sky\RentalRatesPeriod;
use App\Models\Sky\KeyLog;
use App\Models\Sky\Deduction;
use App\Models\Sky\DeductionTranslations;

class DataTable
{
    /**
     * Single or Multiple DataTables on one page.
     *
     * @var string
     */
    protected $request;
    protected $table;
    protected $tables = [];

    /**
     * Creates new instance.
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function setup($model, $table, $options, $internal = null, $modelCount = null)
    {
        $this->setTable($table);
        $this->createTable($table, $options);

        if ($model) {
            if ($modelCount) {
                $count = $modelCount->count();
            } else {
                $count = $model->count();
            }

            $columnsData = $this->getColumnsData();
            // if (!$this->getOption('skipLoading')) {
                if ($this->getOption('translation')) {
                    $model = $model->withTranslation();
                }

                foreach ($columnsData['replaces'] as $replace) {
                    if (isset($replace['replace']['selector'])) {
                        $columnsData['columns'] = array_merge($columnsData['columns'], $replace['replace']['selector']);
                    }
                }

                foreach ($columnsData['aggregates'] as $aggregate) {
                    $model = $model->with($aggregate['aggregate']);
                }

                foreach ($columnsData['pivot'] as $pivot) {
                    if (isset($pivot['pivot']['columns'])) {
                        $model = $model->withPivot(implode(',', $pivot['pivot']['columns']));
                    }
                }

                foreach ($columnsData['count'] as $c) {
                    $model = $model->withCount($c['count']);
                }

                foreach ($columnsData['joins'] as $join) {
                    $model = $model->leftJoin($join['join']['table'], function ($query) use ($join) {
                        $query->on($join['join']['localColumn'], $join['join']['constrain'], $join['join']['foreignColumn']);

                        if (isset($join['join']['whereColumn'])) {
                            $query->where($join['join']['whereColumn'], $join['join']['whereConstrain'], $join['join']['whereValue']);
                        }

                        if (isset($join['join']['whereNull'])) {
                            $query->whereNull($join['join']['whereNull']);
                        }

                        if (isset($join['join']['andWhereColumn'])) {
                            $query->where($join['join']['andWhereColumn'], $join['join']['andWhereConstrain'], $join['join']['andWhereValue']);
                        }
                    });

                    if (isset($join['join']['group'])) {
                        $model = $model->groupBy($join['join']['group']);
                    }
                }

                if ($this->getOption('joins')) {
                    foreach ($this->getOption('joins') as $join) {
                        $model = $model->leftJoin($join['table'], function ($query) use ($join) {
                            $query->on($join['localColumn'], $join['constrain'], $join['foreignColumn']);

                            if (isset($join['whereColumn'])) {
                                $query->where($join['whereColumn'], $join['whereConstrain'], $join['whereValue']);
                            }

                            if (isset($join['whereNull'])) {
                                $query->whereNull($join['whereNull']);
                            }

                            if (isset($join['andWhereColumn'])) {
                                $query->where($join['andWhereColumn'], $join['andWhereConstrain'], $join['andWhereValue']);
                            }
                        });

                        if (isset($join['group'])) {
                            $model = $model->groupBy($join['group']);
                        }
                    }
                }

                foreach ($columnsData['buttons'] as $button) {
                    if (isset($button['button']['selector'])) {
                        $columnsData['columns'] = array_merge($columnsData['columns'], $button['button']['selector']);
                    }
                }

                foreach ($columnsData['appends'] as $append) {
                    if (isset($append['append']['selector'])) {
                        $columnsData['columns'] = array_merge($columnsData['columns'], $append['append']['selector']);
                    }
                }

                foreach ($columnsData['prepends'] as $prepend) {
                    if (isset($prepend['prepend']['selector'])) {
                        $columnsData['columns'] = array_merge($columnsData['columns'], $prepend['prepend']['selector']);
                    }
                }

                foreach ($columnsData['colors'] as $color) {
                    $columnsData['columns'] = array_merge($columnsData['columns'], $color['color']['selector']);
                }

                foreach ($columnsData['links'] as $link) {
                    if (isset($link['link']['selector'])) {
                        $columnsData['columns'] = array_merge($columnsData['columns'], $link['link']['selector']);
                    }
                }

                foreach ($columnsData['profiles'] as $profile) {
                    if (isset($profile['profile']['selector'])) {
                        $columnsData['columns'] = array_merge($columnsData['columns'], $profile['profile']['selector']);
                    }
                }

                foreach ($columnsData['previews'] as $preview) {
                    if (isset($preview['preview']['selector'])) {
                        $columnsData['columns'] = array_merge($columnsData['columns'], $preview['preview']['selector']);
                    }
                }

                foreach ($columnsData['multiDates'] as $date) {
                    if (isset($date['multiDates']['selector'])) {
                        $columnsData['columns'] = array_merge($columnsData['columns'], $date['multiDates']['selector']);
                    }
                }

                foreach ($columnsData['thumbnails'] as $thumbnail) {
                    $columnsData['columns'] = array_merge($columnsData['columns'], $thumbnail['thumbnail']['selector']);
                }

                foreach ($columnsData['files'] as $file) {
                    $columnsData['columns'] = array_merge($columnsData['columns'], $file['file']['selector']);
                }
            // }

            // $count = $model->count();
            //
            // $count = $model->get()->count();
            //
            // if ($this->getOption('count')) {
            //     $model->distinct()->count($this->getOption('count'));
            // }
            $this->setOption('size', ($count <= 100 ? 'small' : ($count <= 1000 ? 'medium' : 'large')));

            if ($this->request->ajax() || $this->request->wantsJson()) {
                $this->setOption('ajax', true);
                if ($internal) {
                    $this->setOption('updateTable', true);
                } else {
                    $this->setOption('reloadTable', true);
                }
                $this->setOption('draw', (int)$this->request->input('draw', 1));
                $this->setOption('recordsTotal', $count);

                $model = $model->select($columnsData['columns']);

                if ($columnsData['selectRaw']) {
                    $model = $model->selectRaw(implode(',', $columnsData['selectRaw']));
                }

                if ($this->getOption('orderByRaw')) {
                    $model = $model->orderByRaw($this->getOption('orderByRaw'));
                }

                $column = $this->request->input('columns.' . $this->request->input('order.0.column') . '.data', $columnsData['orderByColumn']);
                $dir = $this->request->input('order.0.dir', $this->getOption('order'));
                $model = $model->orderBy($column, $dir);

                if ($this->getOption('orderByColumnExtra')) {
                    foreach ($this->getOption('orderByColumnExtra') as $col => $order) {
                        $model = $model->orderBy($col, $order);
                    }
                }

                if ($this->request->input('search.value')) {
                    $this->setOption('search', true);

                    $model = $model->where(function ($query) {
                        $i = 0;
                        foreach ($this->getOption('columns') as $column) {
                            if (isset($column['search'])) {
                                if ($i == 0) {
                                    $query->where($column['selector'], 'like', '%' . $this->request->input('search.value') . '%');
                                } else {
                                    $query->orWhere($column['selector'], 'like', '%' . $this->request->input('search.value') . '%');
                                }
                            }
                            $i++;
                        }
                    });

                    $this->setOption('recordsFiltered', $model->count());
                } else {
                    $this->setOption('recordsFiltered', $count);
                }

                if ($this->request->input('length') > 0) { // All = -1
                    if ($this->request->input('start') > 0) {
                        $model = $model->skip($this->request->input('start'));
                    }

                    $model = $model->take($this->request->input('length'));
                }

                $model = $model->get();

                $originalData = $model;
                $data = $model->toArray();

                if (count($columnsData['processors'])) {
                    $data = $this->process($originalData, $columnsData['processors']);
                }

                if (count($columnsData['calculateMmFees'])) {
                    $data = $this->calculateMmFees($originalData, $this->request->input('type'), $this->request->input('year'));
                }

                if (count($columnsData['calculateCommunalFees'])) {
                    $data = $this->calculateCommunalFees($originalData, $this->request->input('type'), $this->request->input('year'));
                }

                if (count($columnsData['calculatePoolUsage'])) {
                    $data = $this->calculatePoolUsage($originalData, $this->request->input('type'), $this->request->input('year'));
                }

                if (count($columnsData['calculateRentalOptions'])) {
                    if (is_array($columnsData['calculateRentalOptions'][0]['calculateRentalOptions'])) {
                        $option = $columnsData['calculateRentalOptions'][0]['calculateRentalOptions']['option'] ?? $this->request->input('rental');
                        $type = $columnsData['calculateRentalOptions'][0]['calculateRentalOptions']['type'] ?? $this->request->input('type');
                        $skipAmounts = $columnsData['calculateRentalOptions'][0]['calculateRentalOptions']['skipAmounts'] ?? null;
                        $deductPayments = $columnsData['calculateRentalOptions'][0]['calculateRentalOptions']['deductPayments'] ?? true;
                    } else {
                        $option = $this->request->input('rental');
                        $type = $this->request->input('type');
                        $skipAmounts = $this->request->input('skipAmounts');
                        $deductPayments = true;
                    }
                    $data = $this->calculateRentalOptions($originalData, $this->request->input('year'), $option, $type, $skipAmounts, $deductPayments);
                }

                if (count($columnsData['replaces'])) {
                    $data = $this->replace($data, $columnsData['replaces']);
                }

                if (count($columnsData['prefer'])) {
                    $data = $this->prefer($data, $columnsData['prefer']);
                }

                if (count($columnsData['aggregates'])) {
                    $data = $this->aggregate($data, $columnsData['aggregates']);
                }

                if (count($columnsData['pivot'])) {
                    $data = $this->pivot($data, $columnsData['pivot']);
                }

                if (count($columnsData['count'])) {
                    $data = $this->count($data, $columnsData['count']);
                }

                if (count($columnsData['buttons'])) {
                    $data = $this->button($data, $columnsData['buttons']);
                }

                if (count($columnsData['appends'])) {
                    $data = $this->append($data, $columnsData['appends']);
                }

                if (count($columnsData['prepends'])) {
                    $data = $this->prepend($data, $columnsData['prepends']);
                }

                if (count($columnsData['colors'])) {
                    $data = $this->color($data, $columnsData['colors']);
                }

                if (count($columnsData['thumbnails'])) {
                    $data = $this->thumbnail($data, $columnsData['thumbnails']);
                }

                if (count($columnsData['files'])) {
                    $data = $this->file($data, $columnsData['files']);
                }

                if (count($columnsData['download-files'])) {
                    $data = $this->downloadFile($data, $columnsData['download-files']);
                }

                if (count($columnsData['sum'])) {
                    $data = $this->sum($data, $columnsData['sum']);
                }

                if (count($columnsData['transliterate'])) {
                    $data = $this->transliterate($data, $columnsData['transliterate']);
                }

                if (count($columnsData['links'])) {
                    $data = $this->link($data, $columnsData['links']);
                }

                if (count($columnsData['profiles'])) {
                    $data = $this->profile($data, $columnsData['profiles']);
                }

                if (count($columnsData['dropdowns'])) {
                    $data = $this->dropdown($data, $columnsData['dropdowns']);
                }

                if (count($columnsData['previews'])) {
                    $data = $this->preview($data, $columnsData['previews']);
                }

                if (count($columnsData['multiDates'])) {
                    $data = $this->multiDates($data, $columnsData['multiDates']);
                }

                if (count($columnsData['statuses'])) {
                    $data = $this->status($data, $columnsData['statuses']);
                }

                if (count($columnsData['filesizes'])) {
                    $data = $this->filesize($data, $columnsData['filesizes']);
                }

                if (count($columnsData['dates'])) {
                    $data = $this->date($data, $columnsData['dates']);
                }

                if (count($columnsData['data'])) {
                    $data = $this->data($data, $columnsData['data']);
                }

                if (count($columnsData['trans'])) {
                    $data = $this->trans($data, $columnsData['trans']);
                }

                if (!$this->getOption('skipInfo')) {
                    if (count($columnsData['info'])) {
                        $data = $this->info($data, $columnsData['info']);
                    }
                }

                $this->setOption('data', $data);
            } else {
                $this->setOption('count', $count);
                $this->setOption('ajax', $count > \Config::get('datatables.clientSideLimit'));

                if (!$this->getOption('skipLoading')) {
                    if (!$this->getOption('ajax')) {
                        $model = $model->select($columnsData['columns']);

                        if ($columnsData['selectRaw']) {
                            $model = $model->selectRaw(implode(',', $columnsData['selectRaw']));
                        }

                        if ($this->getOption('orderByRaw')) {
                            $model = $model->orderByRaw($this->getOption('orderByRaw'));
                        }

                        $model = $model->orderBy($columnsData['orderByColumn'], $this->getOption('order'));

                        if ($this->getOption('orderByColumnExtra')) {
                            foreach ($this->getOption('orderByColumnExtra') as $col => $order) {
                                $model = $model->orderBy($col, $order);
                            }
                        }

                        $model = $model->get();

                        $originalData = $model;
                        $data = $model->toArray();

                        if (count($columnsData['processors'])) {
                            $data = $this->process($originalData, $columnsData['processors']);
                        }

                        if (count($columnsData['calculateMmFees'])) {
                            $data = $this->calculateMmFees($originalData, $this->request->input('type'), $this->request->input('year'));
                        }

                        if (count($columnsData['calculateCommunalFees'])) {
                            $data = $this->calculateCommunalFees($originalData, $this->request->input('type'), $this->request->input('year'));
                        }

                        if (count($columnsData['calculatePoolUsage'])) {
                            $data = $this->calculatePoolUsage($originalData, $this->request->input('type'), $this->request->input('year'));
                        }

                        if (count($columnsData['calculateRentalOptions'])) {
                            if (is_array($columnsData['calculateRentalOptions'][0]['calculateRentalOptions'])) {
                                $option = $columnsData['calculateRentalOptions'][0]['calculateRentalOptions']['option'] ?? $this->request->input('rental');
                                $type = $columnsData['calculateRentalOptions'][0]['calculateRentalOptions']['type'] ?? $this->request->input('type');
                                $skipAmounts = $columnsData['calculateRentalOptions'][0]['calculateRentalOptions']['skipAmounts'] ?? null;
                                $deductPayments = $columnsData['calculateRentalOptions'][0]['calculateRentalOptions']['deductPayments'] ?? true;
                            } else {
                                $option = $this->request->input('rental');
                                $type = $this->request->input('type');
                                $skipAmounts = $this->request->input('skipAmounts');
                                $deductPayments = true;
                            }
                            $data = $this->calculateRentalOptions($originalData, $this->request->input('year'), $option, $type, $skipAmounts, $deductPayments);
                        }

                        if (count($columnsData['replaces'])) {
                            $data = $this->replace($data, $columnsData['replaces']);
                        }

                        if (count($columnsData['prefer'])) {
                            $data = $this->prefer($data, $columnsData['prefer']);
                        }

                        if (count($columnsData['aggregates'])) {
                            $data = $this->aggregate($data, $columnsData['aggregates']);
                        }

                        if (count($columnsData['pivot'])) {
                            $data = $this->pivot($data, $columnsData['pivot']);
                        }

                        if (count($columnsData['count'])) {
                            $data = $this->count($data, $columnsData['count']);
                        }

                        if (count($columnsData['buttons'])) {
                            $data = $this->button($data, $columnsData['buttons']);
                        }

                        if (count($columnsData['appends'])) {
                            $data = $this->append($data, $columnsData['appends']);
                        }

                        if (count($columnsData['prepends'])) {
                            $data = $this->prepend($data, $columnsData['prepends']);
                        }

                        if (count($columnsData['colors'])) {
                            $data = $this->color($data, $columnsData['colors']);
                        }

                        if (count($columnsData['thumbnails'])) {
                            $data = $this->thumbnail($data, $columnsData['thumbnails']);
                        }

                        if (count($columnsData['files'])) {
                            $data = $this->file($data, $columnsData['files']);
                        }

                        if (count($columnsData['download-files'])) {
                            $data = $this->downloadFiles($data, $columnsData['download-files']);
                        }

                        if (count($columnsData['sum'])) {
                            $data = $this->sum($data, $columnsData['sum']);
                        }

                        if (count($columnsData['transliterate'])) {
                            $data = $this->transliterate($data, $columnsData['transliterate']);
                        }

                        if (count($columnsData['links'])) {
                            $data = $this->link($data, $columnsData['links']);
                        }

                        if (count($columnsData['profiles'])) {
                            $data = $this->profile($data, $columnsData['profiles']);
                        }

                        if (count($columnsData['dropdowns'])) {
                            $data = $this->dropdown($data, $columnsData['dropdowns']);
                        }

                        if (count($columnsData['previews'])) {
                            $data = $this->preview($data, $columnsData['previews']);
                        }

                        if (count($columnsData['multiDates'])) {
                            $data = $this->multiDates($data, $columnsData['multiDates']);
                        }

                        if (count($columnsData['statuses'])) {
                            $data = $this->status($data, $columnsData['statuses']);
                        }

                        if (count($columnsData['filesizes'])) {
                            $data = $this->filesize($data, $columnsData['filesizes']);
                        }

                        if (count($columnsData['dates'])) {
                            $data = $this->date($data, $columnsData['dates']);
                        }

                        if (count($columnsData['data'])) {
                            $data = $this->data($data, $columnsData['data']);
                        }

                        if (count($columnsData['trans'])) {
                            $data = $this->trans($data, $columnsData['trans']);
                        }

                        if (!$this->getOption('skipInfo')) {
                            if (count($columnsData['info'])) {
                                $data = $this->info($data, $columnsData['info']);
                            }
                        }

                        $this->setOption('data', $data);
                    }
                }
            }
        } else {
            $count = count($this->getOption('data'));
            $this->setOption('size', ($count <= 100 ? 'small' : ($count <= 1000 ? 'medium' : 'large')));
            $this->setOption('count', $count);
            $this->setOption('ajax', $count > \Config::get('datatables.clientSideLimit'));
        }
    }

    public function getTable()
    {
        return $this->table;
    }

    public function setTable($table)
    {
        $this->table = $table;
    }

    public function createTable($table, $options)
    {
        $this->tables[$table] = isset($this->tables[$table]) ? array_replace_recursive($options, $this->tables[$table]) : $options;
    }

    public function getTables($table = null)
    {
        return $table ? $this->tables[$table] : $this->tables;
    }

    public function getOption($key)
    {
        return isset($this->tables[$this->getTable()][$key]) ? $this->tables[$this->getTable()][$key] : null;
    }

    public function getOptions($table = null)
    {
        return $this->tables[$table ?: $this->getTable()];
    }

    public function setOption($key, $value, $table = null)
    {
        $this->tables[$table ?: $this->getTable()][$key] = $value;
    }

    public function setOptions($options, $table = null)
    {
        $this->tables[$table ?: $this->getTable()] = $options;
    }

    public function getColumnsData()
    {
        $columnsData = ['selectRaw' => [], 'download-files' => [], 'sum' => [], 'transliterate' => [], 'processors' => [], 'info' => [], 'trans' => [], 'data' => [], 'colors' => [], 'replaces' => [], 'prefer' => [], 'prepends' => [], 'buttons' => [], 'appends' => [], 'profiles' => [], 'dropdowns' => [], 'previews' => [], 'multiDates' => [], 'links' => [], 'statuses' => [], 'thumbnails' => [], 'files' => [], 'filesizes' => [], 'calculateMmFees' => [], 'calculateCommunalFees' => [], 'calculatePoolUsage' => [], 'calculateRentalOptions' => [], 'dates' => [], 'joins' => [], 'aggregates' => [], 'pivot' => [], 'count' => []];
        $columns = array_where($this->getOption('columns'), function ($key, $column) use (&$columnsData) {
            $skip = false;

            if (isset($column['aggregate'])) {
                array_push($columnsData['aggregates'], $column);
                $skip = true;
            }

            if (isset($column['pivot'])) {
                array_push($columnsData['pivot'], $column);
                $skip = true;
            }

            if (isset($column['count'])) {
                array_push($columnsData['count'], $column);
                $skip = true;
            }

            if (isset($column['process'])) {
                array_push($columnsData['processors'], $column);
            }

            if (isset($column['join'])) {
                array_push($columnsData['joins'], $column);
            }

            if (isset($column['buttons'])) {
                array_push($columnsData['buttons'], $column);
            }

            if (isset($column['append'])) {
                array_push($columnsData['appends'], $column);
            }

            if (isset($column['prepend'])) {
                array_push($columnsData['prepends'], $column);
            }

            if (isset($column['color'])) {
                array_push($columnsData['colors'], $column);
            }

            if (isset($column['replace'])) {
                array_push($columnsData['replaces'], $column);
            }

            if (isset($column['prefer'])) {
                array_push($columnsData['prefer'], $column);
            }

            if (isset($column['link'])) {
                array_push($columnsData['links'], $column);
            }

            if (isset($column['profile'])) {
                array_push($columnsData['profiles'], $column);
            }

            if (isset($column['dropdown'])) {
                array_push($columnsData['dropdowns'], $column);
            }

            if (isset($column['preview'])) {
                array_push($columnsData['previews'], $column);
            }

            if (isset($column['multiDates'])) {
                array_push($columnsData['multiDates'], $column);
            }

            if (isset($column['status'])) {
                array_push($columnsData['statuses'], $column);
            }

            if (isset($column['thumbnail'])) {
                array_push($columnsData['thumbnails'], $column);
            }

            if (isset($column['file'])) {
                array_push($columnsData['files'], $column);
            }

            if (isset($column['download-file'])) {
                $columnsData['download-files'][$column['download-file']] = $column['id'];
            }

            if (isset($column['sum'])) {
                array_push($columnsData['sum'], $column);
            }

            if (isset($column['transliterate'])) {
                array_push($columnsData['transliterate'], $column);
            }

            if (isset($column['filesize'])) {
                array_push($columnsData['filesizes'], $column);
            }

            if (isset($column['calculateMmFees'])) {
                array_push($columnsData['calculateMmFees'], $column);
            }

            if (isset($column['calculateCommunalFees'])) {
                array_push($columnsData['calculateCommunalFees'], $column);
            }

            if (isset($column['calculatePoolUsage'])) {
                array_push($columnsData['calculatePoolUsage'], $column);
            }

            if (isset($column['calculateRentalOptions'])) {
                array_push($columnsData['calculateRentalOptions'], $column);
            }

            if (isset($column['date'])) {
                array_push($columnsData['dates'], $column);
            }

            if (isset($column['data'])) {
                array_push($columnsData['data'], $column);
            }

            if (isset($column['info'])) {
                array_push($columnsData['info'], $column);
            }

            if (isset($column['trans'])) {
                array_push($columnsData['trans'], $column);
            }

            if (isset($column['selectRaw'])) {
                array_push($columnsData['selectRaw'], $column['selectRaw']);
            }

            if ($skip) {
                return false;
            } else {
                return true;
            }
        });

        $columnsData['columns'] = array_column($columns, 'selector');
        if ($this->getOption('selectors')) {
            $columnsData['columns'] = array_merge($columnsData['columns'], $this->getOption('selectors'));
        }
        $columnsData['orderByColumn'] = (is_numeric($this->getOption('orderByColumn')) ? $this->getOption('columns')[$this->getOption('orderByColumn')]['selector'] : $this->getOption('orderByColumn'));

        return $columnsData;
    }

    public function process($data, $processors)
    {
        $views = Views::withTranslation()->select('view_translations.slug', 'views.id')->leftJoin('view_translations', 'view_translations.view_id', '=', 'views.id')->where('view_translations.locale', \Locales::getCurrent())->orderBy('view_translations.name')->get();

        foreach ($processors as $process) {
            $method = 'process' . $process['process'];
            $data = $this->$method($data, $views);
        }

        return $data->toArray();
    }

    public function processRentalRatesA1($data, $views = [])
    {
        $newData = $data->map(function ($item, $key) use ($views) {
            foreach ($views as $view) {
                $value = $item->rates->where('room', 'one-bed')->where('view', $view->slug)->first();
                $item->a1 .= '<input class="form-control form-control-rental-rates" name="dates[' . $item->id . '][one-bed][' . $view->slug . ']" type="text" value="' . ($value ? $value->rate : 0) . '">';
            }
            return $item;
        });

        return $newData;
    }

    public function processRentalRatesA2($data, $views = [])
    {
        $newData = $data->map(function ($item, $key) use ($views) {
            foreach ($views as $view) {
                $value = $item->rates->where('room', 'two-bed')->where('view', $view->slug)->first();
                $item->a2 .= '<input class="form-control form-control-rental-rates" name="dates[' . $item->id . '][two-bed][' . $view->slug . ']" type="text" value="' . ($value ? $value->rate : 0) . '">';
            }
            return $item;
        });

        return $newData;
    }

    public function processRentalRatesA3($data, $views = [])
    {
        $newData = $data->map(function ($item, $key) use ($views) {
            foreach ($views as $view) {
                $value = $item->rates->where('room', 'three-bed')->where('view', $view->slug)->first();
                $item->a3 .= '<input class="form-control form-control-rental-rates" name="dates[' . $item->id . '][three-bed][' . $view->slug . ']" type="text" value="' . ($value ? $value->rate : 0) . '">';
            }
            return $item;
        });

        return $newData;
    }

    public function processRentalRatesS($data, $views = [])
    {
        $newData = $data->map(function ($item, $key) use ($views) {
            foreach ($views as $view) {
                $value = $item->rates->where('room', 'studio')->where('view', $view->slug)->first();
                $item->s .= '<input class="form-control form-control-rental-rates" name="dates[' . $item->id . '][studio][' . $view->slug . ']" type="text" value="' . ($value ? $value->rate : 0) . '">';
            }
            return $item;
        });

        return $newData;
    }

    public function processRentalRatesA1S($data)
    {
        $newData = $data->map(function ($item, $key) {
            $value = $item->rates->where('room', 'one-bed')->whereIn('view', ['sea', 'side-sea'])->first();
            $item->a1s = '<input class="form-control form-control-rental-rates" name="dates[' . $item->id . '][one-bed][sea]" type="text" value="' . ($value ? $value->rate : 0) . '">';
            return $item;
        });

        return $newData;
    }

    public function processRentalRatesA1P($data)
    {
        $newData = $data->map(function ($item, $key) {
            $value = $item->rates->where('room', 'one-bed')->where('view', 'park')->first();
            $item->a1p = '<input class="form-control form-control-rental-rates" name="dates[' . $item->id . '][one-bed][park]" type="text" value="' . ($value ? $value->rate : 0) . '">';
            return $item;
        });

        return $newData;
    }

    public function processRentalRatesA2S($data)
    {
        $newData = $data->map(function ($item, $key) {
            $value = $item->rates->where('room', 'two-bed')->whereIn('view', ['sea', 'side-sea'])->first();
            $item->a2s = '<input class="form-control form-control-rental-rates" name="dates[' . $item->id . '][two-bed][sea]" type="text" value="' . ($value ? $value->rate : 0) . '">';
            return $item;
        });

        return $newData;
    }

    public function processRentalRatesA2P($data)
    {
        $newData = $data->map(function ($item, $key) {
            $value = $item->rates->where('room', 'two-bed')->where('view', 'park')->first();
            $item->a2p = '<input class="form-control form-control-rental-rates" name="dates[' . $item->id . '][two-bed][park]" type="text" value="' . ($value ? $value->rate : 0) . '">';
            return $item;
        });

        return $newData;
    }

    public function processRentalRatesA3S($data)
    {
        $newData = $data->map(function ($item, $key) {
            $value = $item->rates->where('room', 'three-bed')->whereIn('view', ['sea', 'side-sea'])->first();
            $item->a3s = '<input class="form-control form-control-rental-rates" name="dates[' . $item->id . '][three-bed][sea]" type="text" value="' . ($value ? $value->rate : 0) . '">';
            return $item;
        });

        return $newData;
    }

    public function processRentalRatesA3P($data)
    {
        $newData = $data->map(function ($item, $key) {
            $value = $item->rates->where('room', 'three-bed')->where('view', 'park')->first();
            $item->a3p = '<input class="form-control form-control-rental-rates" name="dates[' . $item->id . '][three-bed][park]" type="text" value="' . ($value ? $value->rate : 0) . '">';
            return $item;
        });

        return $newData;
    }

    public function processRentalRatesSS($data)
    {
        $newData = $data->map(function ($item, $key) {
            $value = $item->rates->where('room', 'studio')->whereIn('view', ['sea', 'side-sea'])->first();
            $item->ss = '<input class="form-control form-control-rental-rates" name="dates[' . $item->id . '][studio][side-sea]" type="text" value="' . ($value ? $value->rate : 0) . '">';
            return $item;
        });

        return $newData;
    }

    public function processRentalRatesSP($data)
    {
        $newData = $data->map(function ($item, $key) {
            $value = $item->rates->where('room', 'studio')->where('view', 'park')->first();
            $item->sp = '<input class="form-control form-control-rental-rates" name="dates[' . $item->id . '][studio][sea]" type="text" value="' . ($value ? $value->rate : 0) . '">';
            return $item;
        });

        return $newData;
    }

    public function replace($data, $replaces)
    {
        foreach ($data as $key => $items) {
            foreach ($replaces as $replace) {
                if (array_key_exists($replace['id'], $items) || (isset($replace['replace']['id']) && array_key_exists($replace['replace']['id'], $items))) {
                    if (isset($replace['replace']['rules'])) {
                        foreach ($replace['replace']['rules'] as $rules) {
                            $id = $items[isset($rules['column']) ? $rules['column'] : $replace['id']];
                            if (array_key_exists('valueNot', $rules)) {
                                $condition = $id != $rules['valueNot'];
                            } else {
                                $condition = ($id == $rules['value']);
                            }

                            if ($condition) {
                                if (isset($rules['color'])) {
                                    $data[$key][$replace['id']] = '<span style="color: ' . $rules['color'] . ';">' . $rules['text'] . '</span>';
                                } elseif (isset($rules['bold'])) {
                                    $data[$key][$replace['id']] = '<strong>' . $data[$key][$replace['id']] . '</strong>';
                                } elseif (isset($rules['checkbox'])) {
                                    $data[$key]['checkbox'] = '<input type="checkbox">';
                                } elseif (isset($rules['text'])) {
                                    $data[$key][$replace['id']] = $rules['text'];
                                }
                            } else {
                                if (isset($rules['checkbox'])) {
                                    $data[$key]['checkbox'] = '';
                                }
                            }
                        }
                    } else {
                        if (isset($replace['replace']['printf'])) {
                            $data[$key][$replace['id']] = vsprintf($replace['replace']['printf'], array_map(function ($value) use ($data, $key) {
                                return $data[$key][$value];
                            }, $replace['replace']['selector']));
                        } elseif (isset($replace['replace']['simpleText'])) {
                            $data[$key][$replace['id']] = $replace['replace']['simpleText'];
                        } elseif (isset($replace['replace']['array'])) {
                            if (array_key_exists($data[$key][$replace['id']], $replace['replace']['array'])) {
                                $data[$key][$replace['id']] = $replace['replace']['array'][$data[$key][$replace['id']]];
                            }
                        } else {
                            $data[$key][$replace['id']] = $data[$key][$replace['replace']['text']];
                        }
                    }
                }
            }
        }

        return $data;
    }

    public function prefer($data, $prefers)
    {
        foreach ($data as $key => $items) {
            foreach ($prefers as $prefer) {
                if ($data[$key][$prefer['prefer']]) {
                    if ($prefer['prefer'] == 'mm_for_years') {
                        $value = str_replace(',', ', ', $data[$key][$prefer['prefer']]);
                    } else {
                        $value = $data[$key][$prefer['prefer']];
                    }
                } else {
                    $value = $data[$key][$prefer['id']];
                }

                $data[$key][$prefer['id']] = $value;
            }
        }

        return $data;
    }

    public function aggregate($data, $aggregates)
    {
        foreach ($data as $key => $items) {
            foreach ($aggregates as $aggregate) {
                $relation = snake_case($aggregate['aggregate']);

                if (isset($aggregate['aggregateFunction']) && $aggregate['aggregateFunction'] == 'list-admins') {
                    $data[$key][$aggregate['id']] = implode(', ', array_column($items[$relation], 'name'));
                } else {
                    if (count($items[$relation])) {
                        $data[$key][$aggregate['id']] = $items[$relation][0]['aggregate'];
                    } else {
                        $data[$key][$aggregate['id']] = 0;
                    }
                }

                unset($data[$key][$relation]);
            }
        }

        return $data;
    }

    public function pivot($data, $pivots)
    {
        foreach ($data as $key => $items) {
            foreach ($pivots as $pivot) {
                if (isset($pivot['pivot']['rules'])) {
                    $value = $data[$key]['pivot'][$pivot['selector']];
                    foreach ($pivot['pivot']['rules'] as $val => $options) {
                        if ($value == $val) {
                            $data[$key][$pivot['id']] = \HTML::image(\App\Helpers\autover('/img/' . \Locales::getNamespace() . '/' . $options['icon']), $options['title'], ['title' => $options['title']]);
                            break;
                        }
                    }
                } else {
                    $data[$key][$pivot['id']] = $data[$key]['pivot'][$pivot['selector']];
                }

                unset($data[$key]['pivot']);
            }
        }

        return $data;
    }

    public function count($data, $count)
    {
        foreach ($data as $key => $items) {
            foreach ($count as $c) {
                $column = $c['count'] . '_count';
                if (count($items[$column])) {
                    $data[$key][$c['id']] = $items[$column];
                } else {
                    $data[$key][$c['id']] = 0;
                }

                unset($data[$key][$column]);
            }
        }

        return $data;
    }

    public function button($data, $buttons)
    {
        foreach ($data as $key => $items) {
            foreach ($buttons as $button) {
                if (Carbon::parse($data[$key]['dto'])->toDateString() < Carbon::now()->toDateString()) {
                    unset($button['buttons'][0]);
                }
                $data[$key][$button['id']] = str_replace('[id]', $data[$key]['id'], implode($button['buttons']));
            }
        }

        return $data;
    }

    public function append($data, $appends)
    {
        foreach ($data as $key => $items) {
            foreach ($appends as $append) {
                if (array_key_exists($append['id'], $items)) {
                    if (is_array($append['append'])) {
                        if (isset($append['append']['rules'])) {
                            foreach ($append['append']['rules'] as $column => $value) {
                                if ($items[$column] == $value) {
                                    $data[$key][$append['id']] .= $append['append']['text'];
                                }
                            }
                        } elseif (isset($append['append']['trans_choice'])) {
                            $data[$key][$append['id']] .= ' ' . trans_choice(\Locales::getNamespace() . '/' . $append['append']['trans_choice'], $data[$key][$append['id']]);
                        } elseif (isset($append['append']['simpleText'])) {
                            $data[$key][$append['id']] .= $append['append']['simpleText'];
                        } else {
                            $data[$key][$append['id']] .= ' ' . $data[$key][$append['append']['text']];
                        }
                    } else {
                        $data[$key][$append['id']] .= ($append['append'] == 'teaser' ? '<br><span class="teaser">' . $data[$key][$append['append']] . '</span>' : ' ' . $data[$key][$append['append']]);
                    }
                }
            }
        }

        return $data;
    }

    public function prepend($data, $prepends)
    {
        foreach ($data as $key => $items) {
            foreach ($prepends as $prepend) {
                if (array_key_exists($prepend['id'], $items)) {
                    if (is_array($prepend['prepend'])) {
                        if (isset($prepend['prepend']['rules'])) {
                            foreach ($prepend['prepend']['rules'] as $column => $value) {
                                if ($items[$column] == $value) {
                                    $data[$key][$prepend['id']] = $prepend['prepend']['text'] . $data[$key][$prepend['id']];
                                }
                            }
                        } else {
                            if (isset($prepend['prepend']['simpleText'])) {
                                $data[$key][$prepend['id']] = $prepend['prepend']['simpleText'] . ' ' . $data[$key][$prepend['id']];
                            } else {
                                $data[$key][$prepend['id']] = $data[$key][$prepend['prepend']['text']] . ' ' . $data[$key][$prepend['id']];
                            }
                        }
                    } else {
                        $data[$key][$prepend['id']] = $data[$key][$prepend['prepend']] . ' ' . $data[$key][$prepend['id']];
                    }
                }
            }
        }

        return $data;
    }

    public function color($data, $colors)
    {
        foreach ($data as $key => $items) {
            foreach ($colors as $color) {
                if (array_key_exists($color['id'], $items)) {
                    $data[$key][$color['id']] = '<span style="color: ' . $items[$color['color']['id']] . ';">' . $data[$key][$color['id']] . '</span>';
                }
            }
        }

        return $data;
    }

    public function link($data, $links)
    {
        foreach ($data as $key => $items) {
            foreach ($links as $link) {
                if (array_key_exists($link['id'], $items)) {
                    if (isset($link['link']['routeParametersPrepend'])) {
                        $parameters = array_map(function ($k, $v) use ($data, $key) {
                            return $data[$key][$k] . ($v ? '/' . $v : '');
                        }, array_keys($link['link']['routeParametersPrepend']), $link['link']['routeParametersPrepend']);
                    } elseif (isset($link['link']['routeParameters'])) {
                        $parameters = array_map(function ($value) use ($data, $key) {
                            return $data[$key][$value];
                        }, $link['link']['routeParameters']);

                        if (isset($link['link']['prepend'])) {
                            $last = count($parameters) - 1;
                            $parameters[$last] = $link['link']['prepend'] . '/' . $parameters[$last];
                        } elseif (isset($link['link']['prepend-last'])) {
                            $last = count($parameters) - 1;
                            $parameters[$last] .= '/' . $link['link']['prepend-last'];
                        }
                    } else {
                        $parameters = ltrim((isset($link['link']['routeParameter']) ? '/' . $data[$key][$link['link']['routeParameter']] : ''), '/');
                    }

                    if (isset($link['link']['rules'])) {
                        foreach ($link['link']['rules'] as $rules) {
                            if (isset($rules['value'])) {
                                if ($items[$rules['column']] == $rules['value']) {
                                    $data[$key][$link['id']] = '<a class="' . (isset($link['link']['class']) ? $link['link']['class'] : '') . (isset($rules['class']) ? ' ' . $rules['class'] : '') . '" href="' . \Locales::route($link['link']['route'], $parameters) . '">' . ((isset($link['link']['icon']) || isset($rules['icon'])) ? '<span class="glyphicon glyphicon-' . (isset($link['link']['icon']) ? $link['link']['icon'] : $rules['icon']) . ' glyphicon-left"></span>' : '') . $data[$key][$link['id']] . '</a>' . (isset($link['link']['append']) ? '<br><span class="teaser">' . $data[$key][$link['link']['append']] . '</span>' : '');
                                    break;
                                }
                            } else {
                                if ($items[$rules['column']]) {
                                    $data[$key][$link['id']] = '<a class="' . (isset($link['link']['class']) ? $link['link']['class'] : '') . (isset($rules['class']) ? ' ' . $rules['class'] : '') . '" href="' . \Locales::route($link['link']['route'], $parameters) . '">' . ((isset($link['link']['icon']) || isset($rules['icon'])) ? '<span class="glyphicon glyphicon-' . (isset($link['link']['icon']) ? $link['link']['icon'] : $rules['icon']) . ' glyphicon-left"></span>' : '') . $data[$key][$link['id']] . '</a>' . (isset($link['link']['append']) ? '<br><span class="teaser">' . $data[$key][$link['link']['append']] . '</span>' : '');
                                    break;
                                }
                            }
                        }
                    } else {
                        if (isset($link['link']['route'])) {
                            $url = \Locales::route($link['link']['route'], $parameters);
                        } else {
                            $url = $data[$key][$link['link']['url']];
                        }

                        $data[$key][$link['id']] = ($url ? '<a' . (isset($link['link']['class']) ? ' class="' . $link['link']['class'] . '"' : '') . ' href="' . $url . '">' : '') . (isset($link['link']['icon']) ? '<span class="glyphicon glyphicon-' . $link['link']['icon'] . ' glyphicon-left"></span>' : '') . $data[$key][$link['id']] . ($url ? '</a>' : '') . (isset($link['link']['append']) ? '<br><span class="teaser">' . $data[$key][$link['link']['append']] . '</span>' : '');
                    }
                }
            }
        }

        return $data;
    }

    public function profile($data, $profiles)
    {
        foreach ($data as $key => $items) {
            foreach ($profiles as $profile) {
                $data[$key][$profile['id']] = '<a title="' . \HTML::entities($profile['profile']['title']) . '" target="_blank" href="' . \Locales::route($profile['profile']['route'], $data[$key][$profile['profile']['routeParameter']]) . '"><span class="glyphicon glyphicon-' . $profile['profile']['icon'] . '"></span></a>';
            }
        }

        return $data;
    }

    public function dropdown($data, $dropdowns)
    {
        foreach ($data as $key => $items) {
            foreach ($dropdowns as $dropdown) {
                if (isset($dropdown['dropdown']['routeParameters'])) {
                    $params = [];
                    $i = 0;
                    foreach ($dropdown['dropdown']['routeParameters'] as $param) {
                        array_push($params, $data[$key][$param]);
                        if (isset($dropdown['dropdown']['routeParametersPrepend'])) {
                            $params[$i] .= '/' . $dropdown['dropdown']['routeParametersPrepend'][$i];
                        }
                        $i++;
                    }
                } else {
                    $params = $data[$key][$dropdown['dropdown']['routeParameter']];
                }

                $menu = '';
                foreach ($dropdown['dropdown']['menu'] as $slug => $name) {
                    $menu .= '<li><a href="' . \Locales::route($dropdown['dropdown']['route'], $params) . '/' . $slug . '">' . $name . '</a></li>';
                }

                if (isset($dropdown['impersonate'])) {
                    $menu .= '<li class="divider"></li><li><a target="_blank" href="' . \Locales::route($dropdown['dropdown']['route']) . '/' . $dropdown['impersonate']['slug'] . '/' . $params . '">' . $dropdown['impersonate']['name'] . '</a></li>';
                }

                $data[$key][$dropdown['id']] = '<div class="submenu"><a class="dropdown-toggle" title="' . \HTML::entities($dropdown['dropdown']['title']) . '" href="' . \Locales::route($dropdown['dropdown']['route'], $params) . '"><span class="caret"></span></a><ul class="dropdown-menu">' . $menu . '</ul></div>';
            }
        }

        return $data;
    }

    public function preview($data, $previews)
    {
        foreach ($data as $key => $items) {
            foreach ($previews as $preview) {
                $data[$key][$preview['id']] = '<a title="' . \HTML::entities($preview['preview']['title']) . '" class="js-preview" href="' . \Locales::route($preview['preview']['route'], $data[$key][$preview['preview']['routeParameter']]) . (isset($preview['preview']['append']) ? '/' . $preview['preview']['append'] : '') . '"><span class="glyphicon glyphicon-' . $preview['preview']['icon'] . '"></span></a>';
            }
        }

        return $data;
    }

    public function status($data, $statuses)
    {
        foreach ($data as $key => $items) {
            foreach ($statuses as $status) {
                if (!array_key_exists($status['id'], $items)) {
                    $items[$status['id']] = null;
                    $data[$key][$status['id']] = null;
                }

                // if (array_key_exists($status['id'], $items)) {
                    if (array_key_exists('test', $status['status'])) {
                        $id = (int) ($data[$key][$status['id']] == $status['status']['test']);
                        $options = $status['status']['rules'][$id];
                        $data[$key][$status['id']] = \HTML::image(\App\Helpers\autover('/img/' . \Locales::getNamespace() . '/' . $options['icon']), $options['title'], ['title' => $options['title'] . (isset($options['appendDate']) ? ': ' . \App\Helpers\displayWindowsDate(Carbon::parse($data[$key][$options['appendDate']], 'Europe/Sofia')->formatLocalized('%d.%m.%Y')) : '')]);
                    } else {
                        foreach ($status['status']['rules'] as $val => $options) {
                            if ($items[$status['id']] == $val) {
                                $data[$key][$status['id']] = '<a class="' . $status['status']['class'] . '" data-ajax-queue="' . $status['status']['queue'] . '" href="' . \Locales::route($status['status']['route'], [$data[$key]['id'], $options['status']]) . '">' . \HTML::image(\App\Helpers\autover('/img/' . \Locales::getNamespace() . '/' . $options['icon']), $options['title']) . '</a>';
                                break;
                            }
                        }
                    }
                // }
            }
        }

        return $data;
    }

    public function thumbnail($data, $thumbnails)
    {
        foreach ($data as $key => $items) {
            foreach ($thumbnails as $thumbnail) {
                if (array_key_exists($thumbnail['id'], $items)) {
                    $uploadDirectory = 'upload/' . $this->getOption('uploadDirectory') . '/' . (isset($thumbnail['thumbnail']['folder']) ? $data[$key][$thumbnail['thumbnail']['folder']] . '/' : (isset($thumbnail['thumbnail']['root']) ? '' : \Config::get('upload.imagesDirectory') . '/')) . $data[$key][$thumbnail['thumbnail']['id']] . '/';
                    $data[$key][$thumbnail['id']] = '<a class="popup" ' . (isset($thumbnail['thumbnail']['title']) ? 'title="' . \HTML::entities($data[$key][$thumbnail['thumbnail']['title']]) . '"' : '') . 'href="' . asset($uploadDirectory . $this->getOption('expandDirectory') . $data[$key][$thumbnail['id']]) . '">' . \HTML::image($uploadDirectory . \Config::get('upload.thumbnailDirectory') . '/' . $data[$key][$thumbnail['id']], isset($data[$key]['name']) ? $data[$key]['name'] : '') . '</a>';
                }
            }
        }

        return $data;
    }

    public function file($data, $files)
    {
        foreach ($data as $key => $items) {
            foreach ($files as $file) {
                if (array_key_exists($file['id'], $items)) {
                    $data[$key][$file['id']] = '<a ' . (isset($file['file']['title']) ? 'title="' . \HTML::entities($data[$key][$file['file']['title']]) . '"' : '') . 'href="' . \Locales::route($file['file']['route'], $data[$key]['id']) . '">' . \HTML::image(\App\Helpers\autover('/img/' . \Locales::getNamespace() . '/ext/' . $data[$key][$file['file']['extension']] . '.png'), (isset($data[$key]['name']) ? $data[$key]['name'] : '')) . (isset($file['file']['keep']) ? ' ' . $data[$key][$file['id']] : '') . '</a>';
                }
            }
        }

        return $data;
    }

    public function filesize($data, $filesizes)
    {
        foreach ($data as $key => $items) {
            foreach ($filesizes as $filesize) {
                if (array_key_exists($filesize['id'], $items)) {
                    $data[$key][$filesize['id']] = \App\Helpers\formatBytes($data[$key][$filesize['id']]);
                }
            }
        }

        return $data;
    }

    public function downloadFiles($data, $files)
    {
        foreach ($data as $key => $items) {
            foreach ($files as $file) {
                $data[$key][$file] = '';
            }

            if (array_key_exists($this->getOption('download-files')['relation'], $items)) {
                foreach ($items[$this->getOption('download-files')['relation']] as $id => $val) {
                    if (array_key_exists($val['type'], $files)) {
                        $data[$key][$files[$val['type']]] = '<a title="' . \HTML::entities(\App\Helpers\formatBytes($val['size'])) . '" href="' . \Locales::route($this->getOption('download-files')['route'], $val['id']) . '">' . \HTML::image(\App\Helpers\autover('/img/' . \Locales::getNamespace() . '/ext/' . $val['extension'] . '.png'), $val['file']) . '</a>';
                    }
                }
            }
        }

        return $data;
    }

    public function sum($data, $columns)
    {
        foreach ($columns as $column) {
            $sum = 0;
            foreach ($data as $item) {
                $sum += $item[$column['id']];
            }
            $this->setOption($column['id'] . '-sum', $sum);
        }

        return $data;
    }

    public function date($data, $dates)
    {
        foreach ($data as $key => $items) {
            foreach ($dates as $date) {
                if (array_key_exists($date['id'], $items)) {
                    $data[$key][$date['id']] = $data[$key][$date['id']] ? \App\Helpers\displayWindowsDate(Carbon::parse($data[$key][$date['id']], 'Europe/Sofia')->formatLocalized($date['date']['format'])) : '';
                }
            }
        }

        return $data;
    }

    public function transliterate($data, $columns)
    {
        foreach ($data as $key => $items) {
            foreach ($columns as $column) {
                if (array_key_exists($column['id'], $items) && in_array($items['language'], $column['transliterate'])) {
                    $data[$key][$column['id']] = Str::ascii($data[$key][$column['id']]);
                }
            }
        }

        return $data;
    }

    public function multiDates($data, $multiDates)
    {
        foreach ($data as $key => $items) {
            foreach ($multiDates as $multiDate) {
                $dates = '';
                $i = 0;
                foreach ($multiDate['multiDates']['selector'] as $selector) {
                    if ($data[$key][$selector]) {
                        $dates .= $data[$key][$selector] . ' / ';
                        if ($i % 2) {
                            $dates = substr($dates, 0, -3);
                            $dates .= '<br>';
                        }
                    }
                    $i++;
                }
                $data[$key][$multiDate['id']] = $dates;
            }
        }

        return $data;
    }

    public function data($data, $arr)
    {
        foreach ($data as $key => $items) {
            foreach ($arr as $values) {
                if (array_key_exists($values['data']['id'], $items)) {
                    $display = $data[$key][$values['data']['id']];

                    if (isset($values['data']['cast']) && $values['data']['cast'] == 'int') {
                        $val = str_pad((int) preg_replace('/[^\d]+/', '', $display), 2, 0, STR_PAD_LEFT);
                    } elseif (isset($values['data']['date'])) {
                        if ($display) {
                            $date = Carbon::createFromFormat($values['data']['format'] ?? 'd.m.Y', $display, 'Europe/Sofia');
                            $val = Carbon::createFromFormat($values['data']['format'] ?? 'd.m.Y', $display, 'Europe/Sofia')->format($values['data']['date']);
                        } else {
                            $date = Carbon::parse($display, 'Europe/Sofia');
                            $val = Carbon::parse($display, 'Europe/Sofia')->format($values['data']['date']);
                        }

                        if (isset($values['data']['expire'])) {
                            if (isset($values['data']['expire']['year'])) {
                                if ($date->addYear($values['data']['expire']['year'])->year < Carbon::now()->year) {
                                    $display = '<span style="color: ' . $values['data']['expire']['color'] . ';">' . $display . '</span>';
                                }
                            } else {
                                if ($date < Carbon::now()) {
                                    $display = '<span style="color: ' . $values['data']['expire']['color'] . ';">' . $display . '</span>';
                                }
                            }
                        }
                    }

                    if (isset($values['data']['append'])) {
                        foreach ($values['data']['append'] as $append) {
                            $string = '';

                            if (isset($append['id'])) {
                                $string = $data[$key][$append['id']];
                            } elseif (isset($append['text'])) {
                                $string = $append['text'];
                            }

                            if (isset($append['function'])) {
                                $string = $append['function']($string);
                            }

                            if ($string) {
                                $display .= sprintf($append['separator'], $string);
                            }
                        }
                    }

                    $data[$key][$values['data']['id']] = [
                        'display' => $display,
                        $values['data']['type'] => $val,
                    ];
                }
            }
        }

        return $data;
    }

    public function info($data, $info)
    {
        if (isset($info[0]['deductions'])) {
            $deductions = DeductionTranslations::where('locale', \Locales::getCurrent())->get();
            $servicesName = $deductions->where('deduction_id', 3)->first()->name;
            $deductionName = $deductions->where('deduction_id', 2)->first()->name;
        }

        foreach ($data as $key => $items) {
            if (isset($info[0]['deductions']) && isset($data[$key]['deduction_id']) && $data[$key]['deduction_id'] == 5 && isset($data[$key]['cleanAmount'])) {
                $servicesAmount = number_format(round(($data[$key]['cleanAmount'] * 1.4) / 10, 2), 2); // LC 40% bonus
                $deductionAmount = number_format(round($data[$key]['cleanAmount'] - $servicesAmount, 2), 2);
            }

            foreach ($info as $value) {
                $value['info'] = is_array($value['info']) ? $value['info'] : ['comments' => $value['info']];

                $content = false;
                foreach ($value['info'] as $k => $v) {
                    if ($k == 'comments') {
                        $deductionComments = null;
                        $outstandingBills = null;

                        if (isset($data[$key]['deduction_id']) && $data[$key]['deduction_id'] == 5 && isset($data[$key]['cleanAmount'])) {
                            $deductionComments = $deductionName . ': &euro; ' . $deductionAmount . '<br>' . $servicesName . ': &euro; ' . $servicesAmount . '<br>';
                        }

                        if (isset($data[$key]['outstanding_bills']) && $data[$key]['outstanding_bills']) {
                            $outstandingBills = 'Outstanding Bills<br>';
                        }

                        if ($data[$key][$v]) {
                            $data[$key][$value['id']] = $data[$key][$value['id']] . '<div class="tooltip tooltip-info-icon-right"><span class="glyphicon glyphicon-info-sign ' . ($outstandingBills ? 'glyphicon-color-red' : 'glyphicon-color-blue') . '"></span><div class="tooltip-content">' . $outstandingBills . $deductionComments . nl2br($data[$key][$v]) . '</div></div>';
                            $content = true;
                        } elseif ($deductionComments) {
                            $data[$key][$value['id']] = $data[$key][$value['id']] . '<div class="tooltip tooltip-info-icon-right"><span class="glyphicon glyphicon-info-sign glyphicon-color-blue"></span><div class="tooltip-content">' . $deductionComments . '</div></div>';
                            $content = true;
                        } elseif ($outstandingBills) {
                            $data[$key][$value['id']] = $data[$key][$value['id']] . '<div class="tooltip tooltip-info-icon-right"><span class="glyphicon glyphicon-info-sign glyphicon-color-red"></span><div class="tooltip-content">' . $outstandingBills . '</div></div>';
                            $content = true;
                        }
                    }

                    if ($k == 'poa' && $data[$key][$v]) {
                        $data[$key][$value['id']] = $data[$key][$value['id']] . '<div class="tooltip tooltip-info-icon-right"><span class="glyphicon glyphicon-thumbs-up"></span><div class="tooltip-content">' . nl2br($data[$key][$v]) . '</div></div>';
                        $content = true;
                    }

                    if ($k == 'array' && is_array($v)) {
                        $rental = false;
                        $content = '';
                        foreach ($v as $id => $title) {
                            if ($data[$key][$id]) {
                                if ($id == 'is_subscribed') {
                                    $data[$key][$id] = trans(\Locales::getNamespace() . '/multiselect.newsletterSubscription.' . $data[$key][$id]);
                                } elseif ($id == 'outstanding_bills') {
                                    $data[$key][$id] = trans(\Locales::getNamespace() . '/multiselect.outstandingBills.' . $data[$key][$id]);
                                } elseif ($id == 'letting_offer') {
                                    $data[$key][$id] = trans(\Locales::getNamespace() . '/multiselect.lettingOffer.' . $data[$key][$id]);
                                } elseif ($id == 'srioc') {
                                    $data[$key][$id] = trans(\Locales::getNamespace() . '/multiselect.srioc.' . $data[$key][$id]);
                                }

                                $content .= '<strong>' . $title . ':</strong> ' . $data[$key][$id] . '<br>';

                                if ($id === 'rental') {
                                    $rental = true;
                                }
                            }
                        }

                        if ($content) {
                            $data[$key][$value['id']] = $data[$key][$value['id']] . '<div class="tooltip tooltip-info-icon-right"><span class="glyphicon glyphicon-info-sign' . ($rental ? ' text-primary' : '') . '"></span><div class="tooltip-content">' . nl2br($content) . '</div></div>';
                        }
                    }
                }

                if (!$content) {
                    $data[$key][$value['id']] .= '<div class="tooltip-spacer"></div>';
                }
            }
        }

        return $data;
    }

    public function trans($data, $trans)
    {
        foreach ($data as $key => $items) {
            foreach ($trans as $value) {
                $data[$key][$value['id']] = trans(\Locales::getNamespace() . '/multiselect.' . $value['trans'] . '.' . $data[$key][$value['id']]);
            }
        }

        return $data;
    }

    public static function calculateMmFees($model, $type, $reportYear)
    {
        $allYears = Year::all();
        $reportYear = $allYears->where('year', $reportYear)->first();
        if (!$reportYear) {
            abort(404);
        }

        $excluded = [];
        foreach ($model as $index => $m) {
            $mmFees = [];
            $mm_covered = 0;
            $contractsForCurrentYear = $m->contracts->filter(function ($value, $key) {
                return count($value->contractYears);
            });

            if ($contractsForCurrentYear->count()) {
                foreach ($m->contracts as $c) {
                    if ($c->contractYears->contains('year', $reportYear->year)) {
                        $mm_covered += $c->rentalContract->mm_covered;
                        $contractYear = $c->contractYears->where('year', $reportYear->year)->first();
                        if ($contractYear->mm_for_years) {
                            $years = explode(',', $contractYear->mm_for_years);
                            $m->mmYears = str_replace(',', ', ', $contractYear->mm_for_years);
                        } else {
                            $years = [$contractYear->mm_for_year]; // $years = [$reportYear->year];
                            $m->mmYears = $contractYear->mm_for_year;
                        }

                        $mmFee = 0;
                        $paid = 0;
                        /*$paidByOwner = 0;
                        $paidByRental = 0;*/

                        foreach ($years as $year) {
                            $year = $reportYear->year == $year ? $reportYear : $allYears->where('year', $year)->first();
                            if ($year) {
                                $mm = $m->buildingMM->where('year_id', $year->id)->first();
                                if ($mm) {
                                    if ($year->year > 2020) {
                                        $mmFeeTax = round(($m->rooms->capacity * $mm->mm_tax) / 1.95583);
                                    } else {
                                        if ($m->mm_tax_formula == 0) {
                                            $mmFeeTax = (($m->apartment_area + $m->common_area + $m->balcony_area) * $mm->mm_tax) + ($m->extra_balcony_area * ($mm->mm_tax / 2));
                                        } elseif ($m->mm_tax_formula == 1) {
                                            $mmFeeTax = $m->total_area * $mm->mm_tax;
                                        }
                                    }

                                    $payments = $m->mmFeesPayments->where('year_id', $year->id);
                                    $currentFee = ($mmFeeTax * $c->rentalContract->mm_covered) / 100;
                                    $mmFees[$year->year] = round($currentFee - $payments->sum('amount'), 2);
                                    $mmFee += $currentFee;

                                    $paid += round($payments->sum('amount'), 2);
                                    /*$paidByRental += round($payments->filter(function ($value, $key) {
                                        return !is_null($value['rental_company_id']);
                                    })->sum('amount'), 2);
                                    $paidByOwner += round($payments->filter(function ($value, $key) {
                                        return !is_null($value['owner_id']);
                                    })->sum('amount'), 2);*/
                                }
                            }
                        }
                    }
                }
            } else {
                $mmFee = 0;
                $paid = 0;
                $m->mmYears = $reportYear->year;
                $mm = $m->buildingMM->where('year_id', $reportYear->id)->first();
                if ($mm) {
                    if ($reportYear->year > 2020) {
                        $mmFeeTax = round(($m->rooms->capacity * $mm->mm_tax) / 1.95583);
                    } else {
                        if ($m->mm_tax_formula == 0) {
                            $mmFeeTax = (($m->apartment_area + $m->common_area + $m->balcony_area) * $mm->mm_tax) + ($m->extra_balcony_area * ($mm->mm_tax / 2));
                        } elseif ($m->mm_tax_formula == 1) {
                            $mmFeeTax = $m->total_area * $mm->mm_tax;
                        }
                    }

                    $payments = $m->mmFeesPayments->where('year_id', $reportYear->id);
                    $mmFees[$reportYear->year] = round($mmFeeTax - $payments->sum('amount'), 2);
                    $mmFee = $mmFeeTax;

                    $paid = round($payments->sum('amount'), 2);
                    /*$paidByRental += round($payments->filter(function ($value, $key) {
                        return !is_null($value['rental_company_id']);
                    })->sum('amount'), 2);
                    $paidByOwner += round($payments->filter(function ($value, $key) {
                        return !is_null($value['owner_id']);
                    })->sum('amount'), 2);*/
                }
            }

            $m->mmFees = $mmFees;
            $m->mmFee = $mmFee;
            $m->mmFeeValue = number_format($mmFee, 2);
            $m->mm_for_year_name = $m->mmYears;

            $mmFee = round($mmFee, 2);
            $amount = $mmFee - $paid;

            if (in_array($type, ['due', 'due-by-owner', 'due-by-rental'])) {
                if ($type === 'due-by-owner') {
                    if ($mm_covered == 100) {
                        array_push($excluded, $index);
                    }
                } elseif ($type === 'due-by-rental') {
                    if ($mm_covered == 0) {
                        array_push($excluded, $index);
                    }
                }

                if ($amount > 0.1) {
                    $m->amount = number_format($amount, 2);
                } else {
                    array_push($excluded, $index);
                }
            } elseif (in_array($type, ['paid', 'paid-by-owner', 'paid-by-rental'])) {
                if ($type === 'paid-by-owner') {
                    if ($mm_covered == 100) {
                        array_push($excluded, $index);
                    }
                } elseif ($type === 'paid-by-rental') {
                    if ($mm_covered == 0) {
                        array_push($excluded, $index);
                    }
                }

                if ($amount > 0.1) {
                    array_push($excluded, $index);
                } else {
                    $m->amount = number_format($mmFee, 2);
                }
            }
        }

        return $model->forget($excluded)->values();
    }

    public static function calculateCommunalFees($model, $type, $year)
    {
        $year = Year::with('fees')->where('year', $year)->firstOrFail();
        $years = Year::whereIn('year', [$year->year, ($year->year + 1)])->pluck('id', 'year')->all();

        $excluded = [];
        foreach ($model as $index => $m) {
            $fees = $year->fees->where('room_id', $m->room_id)->first();
            if ($fees) {
                $communalFeeTax = round($fees->annual_communal_tax / 1.95583);

                $mm_covered = 0;
                $mm_for_year = $year->year;
                $paid = 0;
                /*$paidByOwner = 0;
                $paidByRental = 0;*/

                $contractsForCurrentYear = $m->contracts->filter(function ($value, $key) {
                    return count($value->contractYears);
                });

                if ($contractsForCurrentYear->count()) {
                    foreach ($contractsForCurrentYear as $c) {
                        $mm_covered += $c->rentalContract->mm_covered;
                        /*if ($type === 'pay-by-rental') {
                            $mm_for_year = $c->contractYears->first()->mm_for_year;
                        }*/

                        if (isset($years[$mm_for_year])) {
                            $payments = $m->communalFeesPayments->where('year_id', $years[$mm_for_year]);
                            $paid += round($payments->sum('amount'), 2);
                            /*$paidByRental += round($payments->filter(function ($value, $key) {
                                return !is_null($value['rental_company_id']);
                            })->sum('amount'), 2);
                            $paidByOwner += round($payments->filter(function ($value, $key) {
                                return !is_null($value['owner_id']);
                            })->sum('amount'), 2);*/
                        }
                    }
                } else {
                    if (isset($years[$mm_for_year])) {
                        $payments = $m->communalFeesPayments->where('year_id', $years[$mm_for_year]);
                        $paid = round($payments->sum('amount'), 2);
                        /*$paidByRental = round($payments->filter(function ($value, $key) {
                            return !is_null($value['rental_company_id']);
                        })->sum('amount'), 2);
                        $paidByOwner = round($payments->filter(function ($value, $key) {
                            return !is_null($value['owner_id']);
                        })->sum('amount'), 2);*/
                    }
                }

                // if ($m->number == 'A1-2') { dd($mm_for_year, $mm_covered, $communalFeeTax, $paid, $paidByOwner, $paidByRental); }

                if (isset($years[$mm_for_year])) {
                    $m->mm_for_year = $years[$mm_for_year];
                }
                $m->mm_for_year_name = $mm_for_year;

                if ($type !== 'due' && $type !== 'paid' && $mm_covered > 0 && $mm_covered < 100) {
                    $communalFeeTax = ($communalFeeTax * $mm_covered) / 100;
                }

                $communalFeeTax = round($communalFeeTax, 2);
                $amount = $communalFeeTax - $paid;

                if (in_array($type, ['due', 'due-by-owner', 'due-by-rental'])) {
                    if ($type === 'due-by-owner') {
                        if ($mm_covered == 100) {
                            array_push($excluded, $index);
                        }
                    } elseif ($type === 'due-by-rental') {
                        if ($mm_covered == 0) {
                            array_push($excluded, $index);
                        }
                    }

                    if ($amount > 0.1) {
                        $m->amount = number_format($amount, 2);
                    } else {
                        array_push($excluded, $index);
                    }
                } elseif (in_array($type, ['paid', 'paid-by-owner', 'paid-by-rental'])) {
                    if ($type === 'paid-by-owner') {
                        if ($mm_covered == 100) {
                            array_push($excluded, $index);
                        }
                    } elseif ($type === 'paid-by-rental') {
                        if ($mm_covered == 0) {
                            array_push($excluded, $index);
                        }
                    }

                    if ($amount > 0.1) {
                        array_push($excluded, $index);
                    } else {
                        $m->amount = number_format($communalFeeTax, 2);
                    }
                }
            } else {
                array_push($excluded, $index);
            }
        }

        return $model->forget($excluded)->values();
    }

    public static function calculatePoolUsage($model, $type, $year)
    {
        $year = Year::with('fees')->where('year', $year)->firstOrFail();
        $years = Year::whereIn('year', [$year->year, ($year->year + 1)])->pluck('id', 'year')->all();

        $excluded = [];
        foreach ($model as $index => $m) {
            $contract = $m->poolUsageContracts->where('year_id', $year->id)->first();
            if ($contract && $contract->is_active) {
                $fees = $year->fees->where('room_id', $m->room_id)->first();
                if ($fees) {
                    $poolUsageTax = round($fees->pool_tax / 1.95583);

                    $mm_covered = 0;
                    $mm_for_year = $year->year;
                    $paid = 0;
                    /*$paidByOwner = 0;
                    $paidByRental = 0;*/

                    $contractsForCurrentYear = $m->contracts->filter(function ($value, $key) {
                        return count($value->contractYears);
                    });

                    if ($contractsForCurrentYear->count()) {
                        foreach ($contractsForCurrentYear as $c) {
                            $mm_covered += $c->rentalContract->mm_covered;
                            /*if ($type === 'pay-by-rental') {
                                $mm_for_year = $c->contractYears->first()->mm_for_year;
                            }*/

                            if (isset($years[$mm_for_year])) {
                                $payments = $m->poolUsagePayments->where('year_id', $years[$mm_for_year]);
                                $paid += round($payments->sum('amount'), 2);
                                /*$paidByRental += round($payments->filter(function ($value, $key) {
                                    return !is_null($value['rental_company_id']);
                                })->sum('amount'), 2);
                                $paidByOwner += round($payments->filter(function ($value, $key) {
                                    return !is_null($value['owner_id']);
                                })->sum('amount'), 2);*/
                            }
                        }
                    } else {
                        if (isset($years[$mm_for_year])) {
                            $payments = $m->poolUsagePayments->where('year_id', $years[$mm_for_year]);
                            $paid = round($payments->sum('amount'), 2);
                            /*$paidByRental = round($payments->filter(function ($value, $key) {
                                return !is_null($value['rental_company_id']);
                            })->sum('amount'), 2);
                            $paidByOwner = round($payments->filter(function ($value, $key) {
                                return !is_null($value['owner_id']);
                            })->sum('amount'), 2);*/
                        }
                    }

                    // if ($m->number == 'A1-2') { dd($mm_for_year, $mm_covered, $poolUsageTax, $paid, $paidByOwner, $paidByRental); }

                    if (isset($years[$mm_for_year])) {
                        $m->mm_for_year = $years[$mm_for_year];
                    }
                    $m->mm_for_year_name = $mm_for_year;

                    if ($type !== 'due' && $type !== 'paid' && $mm_covered > 0 && $mm_covered < 100) {
                        $poolUsageTax = ($poolUsageTax * $mm_covered) / 100;
                    }

                    $poolUsageTax = round($poolUsageTax, 2);
                    $amount = $poolUsageTax - $paid;

                    if (in_array($type, ['due', 'due-by-owner', 'due-by-rental'])) {
                        if ($type === 'due-by-owner') {
                            if ($mm_covered == 100) {
                                array_push($excluded, $index);
                            }
                        } elseif ($type === 'due-by-rental') {
                            if ($mm_covered == 0) {
                                array_push($excluded, $index);
                            }
                        }

                        if ($amount > 0.1) {
                            $m->amount = number_format($amount, 2);
                        } else {
                            array_push($excluded, $index);
                        }
                    } elseif (in_array($type, ['paid', 'paid-by-owner', 'paid-by-rental'])) {
                        if ($type === 'paid-by-owner') {
                            if ($mm_covered == 100) {
                                array_push($excluded, $index);
                            }
                        } elseif ($type === 'paid-by-rental') {
                            if ($mm_covered == 0) {
                                array_push($excluded, $index);
                            }
                        }

                        if ($amount > 0.1) {
                            array_push($excluded, $index);
                        } else {
                            $m->amount = number_format($poolUsageTax, 2);
                        }
                    }
                } else {
                    array_push($excluded, $index);
                }
            } else {
                array_push($excluded, $index);
            }
        }

        return $model->forget($excluded)->values();
    }

    public static function calculateRentalOptions($model, $reportYear, $option, $type, $skipAmounts = null, $deductPayments = true)
    {
        $allYears = Year::all();
        $reportYear = $allYears->where('year', $reportYear)->first();
        if (!$reportYear) {
            abort(404);
        }

        if (!$skipAmounts) {
            $notTaxable = Deduction::withTrashed()->where('is_taxable', 0)->pluck('id')->toArray();
            $rentalRates = [];
            $keylogs = [];
        }

        $currentYear = date('Y');
        $excluded = [];
        foreach ($model as $index => $m) {
            $amount = 0;
            $options = [];
            $durations = [];

            $contractsForCurrentYear = $m->contracts->filter(function ($value, $key) {
                return count($value->contractYears);
            });

            if ($contractsForCurrentYear->count()) {
                if ($option === 'non-rental') { // Rental Pool Report
                    $m->options = null;
                    $m->durations = null;
                    foreach ($m->contracts as $c) {
                        if ($c->contractYears->contains('year', $reportYear->year) && is_null($c->deleted_at)) {
                            array_push($excluded, $index);
                            break;
                        }
                    }
                } else { // $option === 'rental' -> All Rental Contracts or $option === id -> Specific rental contract
                    foreach ($m->contracts as $c) {
                        if ((is_numeric($option) && $c->rental_contract_id != $option) || (is_array($option) && !in_array($c->rental_contract_id, $option))) { // Specific rental contract
                            continue;
                        }

                        if ($c->contractYears->contains('year', $reportYear->year)) {
                            if (!$skipAmounts) {
                                $contractYear = $c->contractYears->where('year', $reportYear->year)->first();
                                if ($contractYear->mm_for_years) {
                                    $years = explode(',', $contractYear->mm_for_years);
                                    $m->mmYears = str_replace(',', ', ', $contractYear->mm_for_years);
                                } else {
                                    $years = [$contractYear->mm_for_year]; // $years = [$reportYear->year];
                                    $m->mmYears = $contractYear->mm_for_year;
                                }

                                $rentAmount = 0;
                                $rentAmountValue = $contractYear->price + $contractYear->price_tc;
                                if ($c->rentalContract->rental_payment_id == 9) { // Rental Rates
                                    if (!$rentalRates) {
                                        $rentalRates = RentalRatesPeriod::with('rates')->whereYear('dfrom', '=', $reportYear->year)->get();

                                        $keylogs = [];
                                        foreach ($rentalRates as $period) {
                                            $keylogs[$period->id] = KeyLog::selectRaw('apartment_id, COUNT(*) AS nights')->whereBetween('occupied_at', [Carbon::parse($period->dfrom), Carbon::parse($period->dto)])->groupBy('apartment_id')->get();
                                        }
                                    }

                                    foreach ($rentalRates as $period) {
                                        $nights = 0;

                                        $keylog = $keylogs[$period->id]->where('apartment_id', $m->id)->first();
                                        if ($keylog) {
                                            $nights = $keylog->nights;
                                        }

                                        if ($period->type == 'personal-usage') {
                                            $nights = $nights - 53; // personal usage period
                                        }

                                        if ($nights > 0) {
                                            $rates = $period->rates->where('project', $m->projectSlug)->where('room', $m->roomSlug)->where('view', $m->viewSlug)->first();
                                            if ($rates) {
                                                $rentAmount += $nights * $rates->rate;
                                            }
                                        }
                                    }
                                }

                                $rentAmountValue += $rentAmount;
                                $m->rentAmount = $rentAmount;
                                $m->rentAmountValue = number_format($rentAmountValue, 2);

                                $mmFee = 0;
                                foreach ($years as $year) {
                                    $year = $reportYear->year == $year ? $reportYear : $allYears->where('year', $year)->first();
                                    if ($year) {
                                        $mm = $m->buildingMM->where('year_id', $year->id)->first();
                                        if ($mm) {
                                            if ($year->year > 2020) {
                                                $mmFeeTax = round(($m->rooms->capacity * $mm->mm_tax) / 1.95583);
                                            } else {
                                                if ($m->mm_tax_formula == 0) {
                                                    $mmFeeTax = (($m->apartment_area + $m->common_area + $m->balcony_area) * $mm->mm_tax) + ($m->extra_balcony_area * ($mm->mm_tax / 2));
                                                } elseif ($m->mm_tax_formula == 1) {
                                                    $mmFeeTax = $m->total_area * $mm->mm_tax;
                                                }
                                            }

                                            $MMpayments = 0;
                                            if ($deductPayments) {
                                                $MMpayments = $contractYear->deleted_at ? 0 : $m->mmFeesPayments->where('year_id', $year->id)->sum('amount');
                                            }

                                            $mmFee += (($mmFeeTax - $MMpayments) * $c->rentalContract->mm_covered) / 100;
                                        }
                                    }
                                }
                                $m->mmFee = $mmFee;
                                $m->mmFeeValue = number_format($mmFee, 2);
                                $m->mm_for_year = $year ? $year->year : ($contractYear->mm_for_years ?: $contractYear->mm_for_year);

                                $total = $rentAmount + $contractYear->price + $contractYear->price_tc + $mmFee;
                                $deductions = $contractYear->deductions;
                                $allDeductions = $deductions->pluck('amount')->sum();
                                $m->deductions = $allDeductions;
                                $m->deductionsValue = number_format($allDeductions, 2);
                                $deductionsAmount = $mmFee + $allDeductions;
                                $owner = $m->owners->filter(function ($value, $key) use ($reportYear) {
                                    if (Carbon::parse($value->created_at)->year <= $reportYear->year && (is_null($value->deleted_at) || Carbon::parse($value->deleted_at)->year >= $reportYear->year)) {
                                        return true;
                                    }

                                    return false;
                                })->first(); // this creates n+1 queries: $owner = $m->owners()->withTrashed()->whereYear('created_at', '<=', $year->year)->first();
                                $tax = 0;

                                if ($owner->owner->apply_wt) { // this creates n+1 queries: $apply_wt = $owner->owner()->withTrashed()->first()->apply_wt;
                                    if ($rentAmountValue > 0) {
                                        $realTax = round($total / 100  * $reportYear->corporate_tax, 2);
                                        if (number_format($total - $deductionsAmount - $realTax, 2) == 0) {
                                            $tax = $realTax;
                                        } else {
                                            $deductionsNotTaxable = $deductions->whereIn('deduction_id', $notTaxable)->pluck('amount')->sum();
                                            $tax = (($rentAmountValue - $deductionsNotTaxable) > 0 ? round((($total - $deductionsNotTaxable) / 100) * $reportYear->corporate_tax, 2) : 0);
                                        }
                                    } else {
                                        $tax = $total / 10; // -10%
                                        $total += $tax; // + 10%
                                    }
                                }
                                $m->tax = $tax;
                                $m->taxValue = number_format($tax, 2);

                                $netRent = round($total - $deductionsAmount - $tax, 2);
                                if ($c->rentalContract->rental_payment_id == 9 && $netRent < 0) { // Rental Rates
                                    $netRent = 0;
                                }

                                $m->netRent = $netRent;
                                $m->netRentValue = number_format($netRent, 2);

                                $payments = $contractYear->payments->sum('amount');
                                $m->payments = $payments;
                                $m->paymentsValue = number_format($payments, 2);

                                if ($type == 'due' || $type == 'due-all') {
                                    $amount += round($netRent - $payments, 2);
                                } else {
                                    $amount += round($netRent + $allDeductions, 2);
                                }
                            }

                            if ($currentYear == $reportYear->year) {
                                if (is_null($c->deleted_at)) {
                                    array_push($options, $c->rentalContract->name);
                                    array_push($durations, $c->duration);
                                }
                            } else {
                                array_push($options, $c->rentalContract->name);
                                array_push($durations, $c->duration);
                            }
                        }
                    }

                    if ($options) {
                        $m->options = implode(', ', $options);
                        $m->durations = implode(', ', $durations);

                        if (!$skipAmounts) {
                            if ($contractYear->price > 0 || $amount > 0 || $type == 'due-all') {
                                if ($type == 'due' || $type == 'due-all') {
                                    $m->amount = number_format($amount, 2);
                                } else {
                                    $m->amount = number_format(abs($amount - $m->payments - $m->deductions), 2);
                                }

                                if ($type == 'due' && $m->amount <= 0) {
                                    array_push($excluded, $index);
                                } elseif ($type == 'paid' && $m->payments <= 0 && $m->amount > 0) {
                                    array_push($excluded, $index);
                                }
                            } else {
                                array_push($excluded, $index);
                            }
                        }
                    } else {
                        array_push($excluded, $index);
                    }
                }
            } elseif ($option === 'non-rental' && $reportYear->year == $currentYear) { // Rental Pool Report for current year
                $m->options = null;
                $m->durations = null;
            } else {
                array_push($excluded, $index);
            }
        }

        return $model->forget($excluded)->values();
    }
}
