Дистанция {{$walking['distance']}} метров. Время {{intdiv($walking['time'],3600)}}ч. {{round(($walking['time']%3600)/60)}}мин
Быстрое перемещение(обычно метро) {{$jumps['distance']}} метров за {{intdiv($jumps['time'],3600)}}ч. {{round(($jumps['time']%3600)/60)}}мин
Стояли на одном месте: {{intdiv($stops['time'],3600)}}ч. {{round(($stops['time']%3600)/60)}}мин
