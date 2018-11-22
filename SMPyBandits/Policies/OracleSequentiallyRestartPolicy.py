# -*- coding: utf-8 -*-
r""" An oracle policy for non-stationary bandits, restarting an underlying stationary bandit policy at each breakpoint.

- It runs on top of a simple policy, e.g., :class:`UCB`, and :func:`OracleSequentiallyRestartPolicy` is a wrapper:

    >>> policy = OracleSequentiallyRestartPolicy(nbArms, UCB)
    >>> # use policy as usual, with policy.startGame(), r = policy.choice(), policy.getReward(arm, r)

- It uses the knowledge of the breakpoints to restart the underlying algorithm at each breakpoint.
- It is very simple but impractical: in any real problem it is impossible to know the locations of the breakpoints, but it acts as an efficient baseline.

.. warning:: It is an efficient baseline, but it has no reason to be the best algorithm on a given problem (empirically)! I found that :class:`Policy.DiscountedThompson.DiscountedThompson` is usually the most efficient.
"""
from __future__ import division, print_function  # Python 2 compatibility

__author__ = "Lilian Besson"
__version__ = "0.9"


import numpy as np

try:
    from .with_proba import with_proba
    from .BaseWrapperPolicy import BaseWrapperPolicy
    from .UCB import UCB as DefaultPolicy
except ImportError:
    from with_proba import with_proba
    from BaseWrapperPolicy import BaseWrapperPolicy
    from UCB import UCB as DefaultPolicy


#: Should we reset one arm empirical average or all? Default is ``False`` for this algorithm.
PER_ARM_RESTART = False

#: Should we fully restart the algorithm or simply reset one arm empirical average ?
FULL_RESTART_WHEN_REFRESH = False


# --- The very generic class

class OracleSequentiallyRestartPolicy(BaseWrapperPolicy):
    r""" An oracle policy for non-stationary bandits, restarting an underlying stationary bandit policy at each breakpoint.
    """
    def __init__(self, nbArms,
            changePoints=None,
            full_restart_when_refresh=FULL_RESTART_WHEN_REFRESH,
            per_arm_restart=PER_ARM_RESTART,
            policy=DefaultPolicy,
            lower=0., amplitude=1., *args, **kwargs
        ):
        super(OracleSequentiallyRestartPolicy, self).__init__(nbArms, policy=policy, lower=lower, amplitude=amplitude, *args, **kwargs)

        if changePoints is None:
            changePoints = []
        changePoints = sorted([tau for tau in changePoints if tau > 0])
        self.changePoints = changePoints  #: Locations of the break points (or change points) of the switching bandit problem. If ``None``, an empty list is used.

        self._full_restart_when_refresh = full_restart_when_refresh  # Should we fully restart the algorithm or simply reset one arm empirical average ?
        self._per_arm_restart = per_arm_restart  # Should we reset one arm empirical average or all?

        # Internal memory
        self.all_rewards = [[] for _ in range(self.nbArms)]  #: Keep in memory all the rewards obtained since the last restart on that arm.
        self.last_pulls = np.full(nbArms, -1)  #: Keep in memory the times where each arm was last seen. Start with -1 (never seen)

        # DEBUG
        print("Info: creating a new policy {}, with change points = {}...".format(self, changePoints))  # DEBUG

    def __str__(self):
        return r"OracleRestart-{}($\Upsilon_T={}${})".format(self._policy.__name__, len(self.changePoints), ", Per-Arm" if self._per_arm_restart else "")

    def getReward(self, arm, reward):
        """ Give a reward: increase t, pulls, and update cumulated sum of rewards and update small history (sliding window) for that arm (normalized in [0, 1]).

        - Reset the whole empirical average if the current time step is in the list of change points.
        """
        super(OracleSequentiallyRestartPolicy, self).getReward(arm, reward)
        # Get reward
        reward = (reward - self.lower) / self.amplitude
        # We seen it one more time
        self.last_pulls[arm] += 1
        # Store it in place for the empirical average of that arm
        self.all_rewards[arm].append(reward)
        if self.t in self.changePoints:
            print("For a player {} a change was detected at time {} for arm {} after seeing reward = {}, because this time step is in its list of change points!".format(self, self.t, arm, reward))  # DEBUG

            # Fully restart the algorithm ?!
            if self._full_restart_when_refresh:
                self.startGame(createNewPolicy=False)
            # Or simply reset one of the empirical averages?
            else:
                self.policy.rewards[arm] = np.sum(self.all_rewards[arm])
                self.policy.pulls[arm] = len(self.all_rewards[arm])

            # reset current memory for THIS arm
            if self._per_arm_restart:
                self.last_pulls[arm] = 1
                self.all_rewards[arm] = [reward]
            # or reset current memory for ALL THE arms
            else:
                for other_arm in range(self.nbArms):
                    self.last_pulls[other_arm] = 0
                    self.all_rewards[other_arm] = []
                self.last_pulls[arm] = 1
                self.all_rewards[arm] = [reward]

        # we update the total number of samples available to the underlying policy
        self.policy.t = np.sum(self.last_pulls)
