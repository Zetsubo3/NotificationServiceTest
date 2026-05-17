<?php

namespace App\Filters;

use Illuminate\Database\Eloquent\Builder;

class NotificationFilter
{
    /**
     * Применить фильтры к запросу
     *
     * @param Builder $query
     * @param array $params
     * @return Builder
     */
    public function apply(Builder $query, array $params): Builder
    {
        if (!empty($params['status'])) {
            $query->where('status', $params['status']);
        }

        if (!empty($params['channel'])) {
            $query->where('channel', $params['channel']);
        }

        if (!empty($params['priority'])) {
            $query->where('priority', $params['priority']);
        }

        if (!empty($params['created_at_sort'])) {
            $query->orderBy('created_at', $params['created_at_sort']);
        } else {
            // по умолчанию сортируем по убыванию
            $query->orderBy('created_at', 'desc');
        }

        return $query;
    }
}
