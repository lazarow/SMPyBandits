# -*- coding: utf-8 -*-
"""
Configuration for the experiments including Tournament Selection Policy.
"""
from __future__ import division, print_function  # Python 2 compatibility

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

# Import arms and algorithms
try:
    from Arms import *
    from Policies import *
    from Policies.Experimentals import *
except ImportError:
    from SMPyBandits.Arms import *
    from SMPyBandits.Policies import *
    from SMPyBandits.Policies.Experimentals import *

#: HORIZON : number of time steps of the experiments.
HORIZON = 10000
HORIZON = int(getenv('T', HORIZON))

#: REPETITIONS : number of repetitions of the experiments.
#: Warning: Should be >= 10 to be statistically trustworthy.
REPETITIONS = 10  # Nb of cores, to have exactly one repetition process by cores
REPETITIONS = int(getenv('N', REPETITIONS))

#: To profile the code, turn down parallel computing
DO_PARALLEL = True
DO_PARALLEL = (REPETITIONS > 1 or REPETITIONS == -1) and DO_PARALLEL

#: Number of jobs to use for the parallel computations. -1 means all the CPU cores, 1 means no parallelization.
N_JOBS = -1 if DO_PARALLEL else 1
if CPU_COUNT > 4:  # We are on a server, let's be nice and not use all cores
    N_JOBS = min(CPU_COUNT, max(int(CPU_COUNT / 3), CPU_COUNT - 8))
N_JOBS = int(getenv('N_JOBS', N_JOBS))
if REPETITIONS == -1:
    REPETITIONS = max(N_JOBS, CPU_COUNT)


#: Parameters for the epsilon-greedy and epsilon-... policies.
EPSILON = 0.1
#: Temperature for the Softmax policies.
TEMPERATURE = 0.05

# Parameters for the arms
UNBOUNDED_VARIANCE = 1   #: Variance of unbounded Gaussian arms
VARIANCE = 0.05   #: Variance of Gaussian arms

#: Number of arms for non-hard-coded problems (Bayesian problems)
NB_ARMS = 9
NB_ARMS = int(getenv('K', NB_ARMS))
NB_ARMS = int(getenv('NB_ARMS', NB_ARMS))

#: Default value for the lower value of means
LOWER = 0.
#: Default value for the amplitude value of means
AMPLITUDE = 1.

#: Type of arms for non-hard-coded problems (Bayesian problems)
ARM_TYPE = "Bernoulli"
ARM_TYPE = str(getenv('ARM_TYPE', ARM_TYPE))

# WARNING That's nonsense, rewards of unbounded distributions just don't have lower, amplitude values...
if ARM_TYPE in [
            "UnboundedGaussian",
            # "Gaussian",
        ]:
    LOWER = -5
    AMPLITUDE = 10

LOWER = float(getenv('LOWER', LOWER))
AMPLITUDE = float(getenv('AMPLITUDE', AMPLITUDE))
assert AMPLITUDE > 0, "Error: invalid amplitude = {:.3g} but has to be > 0."  # DEBUG
VARIANCE = float(getenv('VARIANCE', VARIANCE))

ARM_TYPE_str = str(ARM_TYPE)
ARM_TYPE = mapping_ARM_TYPE[ARM_TYPE]

#: True to use bayesian problem
ENVIRONMENT_BAYESIAN = False
ENVIRONMENT_BAYESIAN = getenv('BAYES', str(ENVIRONMENT_BAYESIAN)) == 'True'

#: True to use full-restart Doubling Trick
USE_FULL_RESTART = True
USE_FULL_RESTART = getenv('FULL_RESTART', str(USE_FULL_RESTART)) == 'True'

#: This dictionary configures the experiments
configuration = {
    # --- Duration of the experiment
    "horizon": HORIZON,
    # --- Number of repetition of the experiment (to have an average)
    "repetitions": REPETITIONS,
    # --- Parameters for the use of joblib.Parallel
    "n_jobs": N_JOBS,    # = nb of CPU cores
    "verbosity": 6,      # Max joblib verbosity
    # --- Should we plot the lower-bounds or not?
    "plot_lowerbound": True,  # XXX Default
    # "plot_lowerbound": False,
    # --- Cache rewards: use the same random rewards for the Aggregator[..] and the algorithms
    "cache_rewards": False,
    # --- Arms
    "environment": [
        #{
        #    "arm_type": Bernoulli,
        #    "params": [0.6, 0.55, 0.4]
        #},
        {
            "arm_type": Bernoulli,
            "params": [0.5, 0.4, 0.4, 0.4, 0.4, 0.4, 0.4, 0.4, 0.4, 0.4, 0.4, 0.4, 0.4, 0.4, 0.4, 0.4, 0.4, 0.4, 0.4, 0.4]
        }
    ],
}

try:
    #: Number of arms *in the first environment*
    nbArms = int(configuration['environment'][0]['params']['args']['nbArms'])
except (TypeError, KeyError):
    nbArms = len(configuration['environment'][0]['params'])

#: Warning: if using Exponential or Gaussian arms, gives klExp or klGauss to KL-UCB-like policies!
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

# XXX Huge hack! Use this if you want to modify the legends
configuration.update({
    "append_labels": {
        policyId: cfgpolicy.get("append_label", "")
        for policyId, cfgpolicy in enumerate(configuration["policies"])
        if "append_label" in cfgpolicy
    },
    "change_labels": {
        policyId: cfgpolicy.get("change_label", "")
        for policyId, cfgpolicy in enumerate(configuration["policies"])
        if "change_label" in cfgpolicy
    }
})

print("Loaded experiments configuration from 'configuration_tspol.py' :")
print("configuration['policies'] =", configuration["policies"])  # DEBUG
