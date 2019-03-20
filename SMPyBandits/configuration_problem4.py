# -*- coding: utf-8 -*-
from __future__ import division, print_function

__author__ = "Arkadiusz Nowakowski <nowakowski.arkadiusz@gmail.com>"
__version__ = "0.1"

try:
    from multiprocessing import cpu_count
    CPU_COUNT = cpu_count()  #: Number of CPU on the local machine
except ImportError:
    CPU_COUNT = 1

from os import getenv

if __name__ == '__main__':
    exit(0)

try:
    from Arms import *
    from Policies import *
    from Policies.Experimentals import *
except ImportError:
    from SMPyBandits.Arms import *
    from SMPyBandits.Policies import *
    from SMPyBandits.Policies.Experimentals import *

HORIZON = 200
REPETITIONS = 100
DO_PARALLEL = True
N_JOBS = 8

EPSILON = 0.1
TEMPERATURE = 0.05
USE_FULL_RESTART = True

configuration = {
    "horizon": HORIZON,
    "repetitions": REPETITIONS,
    "n_jobs": N_JOBS,
    "verbosity": 6,
    "plot_lowerbound": False,
    "cache_rewards": False,
    "environment": [
        {
            "arm_type": Bernoulli,
            "params": [0.9, 0.6, 0.6, 0.6, 0.6, 0.6, 0.6, 0.6, 0.6, 0.6]
        }
    ],
}

nbArms = len(configuration['environment'][0]['params'])
klucb = klucb_mapping.get(str(configuration['environment'][0]['arm_type']), klucbBern)

configuration.update({
    "policies": [
        {
            "archtype": UCB,   # This basic UCB is very worse than the other
            "params": {}
        },
        {
            "archtype": Thompson,
            "params": {
                "posterior": Beta,
            }
        },
        {
            "archtype": BayesUCB,
            "params": {
                "posterior": Beta,
            }
        },
        {
            "archtype": AdBandits,
            "params": {
                "alpha": 0.5,
                "horizon": HORIZON,
            }
        },
        {"archtype": TSPol, "params": {} }
    ]
})

print("configuration['policies'] =", configuration["policies"])  # DEBUG
