<?php
return [
    'queue.filepath' => __DIR__ . '/../experiment-queue.json',
    'configuration.template.filepath' => __DIR__ . '/../templates/configuration_template.py',
    'output.dir' => __DIR__ . '/../output',
    'smpybandits.dir' => __DIR__ . '/../../../SMPyBandits',
    'h5tojson' => __DIR__ . '/../../../Scripts/h5tojson.exe',
    'plot.template.filepath' => __DIR__ . '/../templates/plot_template.tex',
    'tables.template.filepath' => __DIR__ . '/../templates/tables_template.tex',
    'pdflatex' => 'pdflatex',
    'nof.jobs' => 4
];