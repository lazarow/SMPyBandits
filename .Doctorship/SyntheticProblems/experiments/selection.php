<?php
return [
    'repetitions' => 100,
    /**
     * Problemy pochodzą z:
     * - https://homes.di.unimi.it/~cesabian/Pubblicazioni/ml-02.pdf
    */
    'arms' => [
        [0.9, 0.8, 0.8, 0.8, 0.7, 0.7, 0.7, 0.6, 0.6, 0.6],
        [0.55, 0.45],
        [0.6, 0.55, 0.4],
        [0.5, 0.4, 0.4, 0.4, 0.4, 0.4, 0.4, 0.4, 0.4, 0.4, 0.4, 0.4, 0.4, 0.4, 0.4, 0.4, 0.4, 0.4, 0.4, 0.4],
        [0.5, 0.42, 0.42, 0.42, 0.42, 0.42, 0.38, 0.38, 0.38, 0.38, 0.38, 0.38, 0.38, 0.38, 0.38, 0.38, 0.38, 0.38, 0.38, 0.38],
        [0.5, 0.3631, 0.449347, 0.48125839],
        [0.5, 0.42, 0.4, 0.4, 0.35, 0.35],
        [0.5, 0.45, 0.425, 0.4, 0.375, 0.35, 0.325, 0.3, 0.275, 0.25, 0.225, 0.2, 0.175, 0.15, 0.125],
        [0.5, 0.48, 0.37, 0.37, 0.37, 0.37, 0.37, 0.37, 0.37, 0.37, 0.37, 0.37, 0.37, 0.37, 0.37, 0.37, 0.37, 0.37, 0.37, 0.37],
        [0.5, 0.45, 0.45, 0.45, 0.45, 0.45, 0.43, 0.43, 0.43, 0.43, 0.43, 0.43, 0.43, 0.43, 0.43, 0.43, 0.43, 0.43, 0.43, 0.43, 0.38, 0.38, 0.38, 0.38, 0.38, 0.38, 0.38, 0.38, 0.38, 0.38],
        [0.51, 0.49],
        [0.97, 0.95]
    ],
    'policies' => [
        ['archtype' => 'TSPolP', 'params' => []],
        # --- Epsilon-... algorithms
        ['archtype' => 'EpsilonGreedy', 'params' => ['epsilon' => '0.1']],
        ['archtype' => 'EpsilonGreedy', 'params' => ['epsilon' => '0.2']],
        ['archtype' => 'EpsilonGreedy', 'params' => ['epsilon' => '0.3']],
        ['archtype' => 'EpsilonGreedy', 'params' => ['epsilon' => '0.4']],
        ['archtype' => 'EpsilonGreedy', 'params' => ['epsilon' => '0.5']],
        ['archtype' => 'EpsilonGreedy', 'params' => ['epsilon' => '0.6']],
        ['archtype' => 'EpsilonGreedy', 'params' => ['epsilon' => '0.7']],
        ['archtype' => 'EpsilonGreedy', 'params' => ['epsilon' => '0.8']],
        ['archtype' => 'EpsilonGreedy', 'params' => ['epsilon' => '0.9']],
        ['archtype' => 'EpsilonDecreasing', 'params' => ['epsilon' => '0.1']],
        ['archtype' => 'EpsilonDecreasing', 'params' => ['epsilon' => '0.2']],
        ['archtype' => 'EpsilonDecreasing', 'params' => ['epsilon' => '0.3']],
        ['archtype' => 'EpsilonDecreasing', 'params' => ['epsilon' => '0.4']],
        ['archtype' => 'EpsilonDecreasing', 'params' => ['epsilon' => '0.5']],
        ['archtype' => 'EpsilonDecreasing', 'params' => ['epsilon' => '0.6']],
        ['archtype' => 'EpsilonDecreasing', 'params' => ['epsilon' => '0.7']],
        ['archtype' => 'EpsilonDecreasing', 'params' => ['epsilon' => '0.8']],
        ['archtype' => 'EpsilonDecreasing', 'params' => ['epsilon' => '0.9']],
        ['archtype' => 'EpsilonExpDecreasing', 'params' => ['epsilon' => '0.1', 'decreasingRate' => 0.005]],
        ['archtype' => 'EpsilonExpDecreasing', 'params' => ['epsilon' => '0.2', 'decreasingRate' => 0.005]],
        ['archtype' => 'EpsilonExpDecreasing', 'params' => ['epsilon' => '0.3', 'decreasingRate' => 0.005]],
        ['archtype' => 'EpsilonExpDecreasing', 'params' => ['epsilon' => '0.4', 'decreasingRate' => 0.005]],
        ['archtype' => 'EpsilonExpDecreasing', 'params' => ['epsilon' => '0.5', 'decreasingRate' => 0.005]],
        ['archtype' => 'EpsilonExpDecreasing', 'params' => ['epsilon' => '0.6', 'decreasingRate' => 0.005]],
        ['archtype' => 'EpsilonExpDecreasing', 'params' => ['epsilon' => '0.7', 'decreasingRate' => 0.005]],
        ['archtype' => 'EpsilonExpDecreasing', 'params' => ['epsilon' => '0.8', 'decreasingRate' => 0.005]],
        ['archtype' => 'EpsilonExpDecreasing', 'params' => ['epsilon' => '0.9', 'decreasingRate' => 0.005]],
        ['archtype' => 'EpsilonFirst', 'params' => ['epsilon' => '0.1', 'horizon' => 'HORIZON']],
        ['archtype' => 'EpsilonFirst', 'params' => ['epsilon' => '0.2', 'horizon' => 'HORIZON']],
        ['archtype' => 'EpsilonFirst', 'params' => ['epsilon' => '0.3', 'horizon' => 'HORIZON']],
        ['archtype' => 'EpsilonFirst', 'params' => ['epsilon' => '0.4', 'horizon' => 'HORIZON']],
        ['archtype' => 'EpsilonFirst', 'params' => ['epsilon' => '0.5', 'horizon' => 'HORIZON']],
        ['archtype' => 'EpsilonFirst', 'params' => ['epsilon' => '0.6', 'horizon' => 'HORIZON']],
        ['archtype' => 'EpsilonFirst', 'params' => ['epsilon' => '0.7', 'horizon' => 'HORIZON']],
        ['archtype' => 'EpsilonFirst', 'params' => ['epsilon' => '0.8', 'horizon' => 'HORIZON']],
        ['archtype' => 'EpsilonFirst', 'params' => ['epsilon' => '0.9', 'horizon' => 'HORIZON']],
        # --- Explore-Then-Commit policies
        ['archtype' => 'ETC_KnownGap', 'params' => ['horizon' => 'HORIZON', "gap" => 0.05]],
        ['archtype' => 'ETC_RandomStop', 'params' => ['horizon' => 'HORIZON']],
        # --- Softmax algorithms
        ['archtype' => 'Softmax', 'params' => ['temperature' => '0.05']],
        ['archtype' => 'SoftmaxDecreasing', 'params' => []],
        ['archtype' => 'SoftMix', 'params' => []],
        ['archtype' => 'SoftmaxWithHorizon', 'params' => ['horizon' => 'HORIZON']],
        # --- Boltzmann-Gumbel algorithms
        ['archtype' => 'BoltzmannGumbel', 'params' => ['C' => '0.5']],
        # --- Probability pursuit algorithm
        ['archtype' => 'ProbabilityPursuit', 'params' => ['beta' => '0.5']],
        # --- Hedge algorithm
        ['archtype' => 'Hedge', 'params' => ['epsilon' => '0.5']],
        ['archtype' => 'HedgeDecreasing', 'params' => []],
        ['archtype' => 'HedgeWithHorizon', 'params' => ['horizon' => 'HORIZON']],
        # --- UCB algorithms
        ['archtype' => 'UCB', 'params' => []],
        ['archtype' => 'UCBlog10', 'params' => []],
        ['archtype' => 'UCBwrong', 'params' => []],
        ['archtype' => 'UCBalpha', 'params' => ['alpha' => 0]],
        ['archtype' => 'UCBalpha', 'params' => ['alpha' => 0.1]],
        ['archtype' => 'UCBalpha', 'params' => ['alpha' => 0.2]],
        ['archtype' => 'UCBalpha', 'params' => ['alpha' => 0.3]],
        ['archtype' => 'UCBalpha', 'params' => ['alpha' => 0.4]],
        ['archtype' => 'UCBalpha', 'params' => ['alpha' => 0.5]],
        ['archtype' => 'UCBalpha', 'params' => ['alpha' => 0.6]],
        ['archtype' => 'UCBalpha', 'params' => ['alpha' => 0.7]],
        ['archtype' => 'UCBalpha', 'params' => ['alpha' => 0.8]],
        ['archtype' => 'UCBalpha', 'params' => ['alpha' => 0.9]],
        ['archtype' => 'UCBalpha', 'params' => ['alpha' => 1]],
        ['archtype' => 'UCBlog10alpha', 'params' => ['alpha' => 0]],
        ['archtype' => 'UCBlog10alpha', 'params' => ['alpha' => 0.1]],
        ['archtype' => 'UCBlog10alpha', 'params' => ['alpha' => 0.2]],
        ['archtype' => 'UCBlog10alpha', 'params' => ['alpha' => 0.3]],
        ['archtype' => 'UCBlog10alpha', 'params' => ['alpha' => 0.4]],
        ['archtype' => 'UCBlog10alpha', 'params' => ['alpha' => 0.5]],
        ['archtype' => 'UCBlog10alpha', 'params' => ['alpha' => 0.6]],
        ['archtype' => 'UCBlog10alpha', 'params' => ['alpha' => 0.7]],
        ['archtype' => 'UCBlog10alpha', 'params' => ['alpha' => 0.8]],
        ['archtype' => 'UCBlog10alpha', 'params' => ['alpha' => 0.9]],
        ['archtype' => 'UCBlog10alpha', 'params' => ['alpha' => 1]],
        ['archtype' => 'UCBmin', 'params' => []],
        ['archtype' => 'UCBplus', 'params' => []],
        ['archtype' => 'UCBrandomInit', 'params' => []],
        ['archtype' => 'UCBV', 'params' => []],
        ['archtype' => 'UCBVtuned', 'params' => []],
        # --- SparseUCB and variants policies for sparse stochastic bandit
        ['archtype' => 'SparseUCB', 'params' => ['alpha' => 4, 'sparsity' => 'min(nbArms, 3)']],
        ['archtype' => 'SparseklUCB', 'params' => ['sparsity' => 'min(nbArms, 3)']],
        # --- MOSS algorithm, like UCB
        ['archtype' => 'MOSS', 'params' => []],
        ['archtype' => 'MOSSH', 'params' => ['horizon' => 'HORIZON']],
        ['archtype' => 'MOSSAnytime', 'params' => ['alpha' => '1.35']],
        # --- Optimally-Confident UCB algorithm
        ['archtype' => 'OCUCB', 'params' => ['eta' => '1.1', 'rho' => '1']],
        # --- CPUCB algorithm, other variant of UCB
        ['archtype' => 'CPUCB', 'params' => []],
        # --- DMED algorithm, similar to klUCB
        ['archtype' => 'DMEDPlus', 'params' => []],
        ['archtype' => 'DMED', 'params' => []],
        # --- Thompson algorithms
        ['archtype' => 'Thompson', 'params' => ['posterior' => 'Beta']],
        ['archtype' => 'Thompson', 'params' => ['posterior' => 'Gauss']],
        ['archtype' => 'ThompsonRobust', 'params' => ['posterior' => 'Beta']],
        # --- KL algorithms
        ['archtype' => 'klUCB', 'params' => ['klucb' => 'klucb']],
        ['archtype' => 'klUCBloglog', 'params' => ['klucb' => 'klucb']],
        ['archtype' => 'klUCBlog10', 'params' => ['klucb' => 'klucb']],
        ['archtype' => 'klUCBloglog10', 'params' => ['klucb' => 'klucb']],
        ['archtype' => 'klUCBPlus', 'params' => ['klucb' => 'klucb']],
        ['archtype' => 'klUCBH', 'params' => ['klucb' => 'klucb', 'horizon' => 'HORIZON']],
        ['archtype' => 'klUCBHPlus', 'params' => ['klucb' => 'klucb', 'horizon' => 'HORIZON']],
        ['archtype' => 'klUCBPlusPlus', 'params' => ['klucb' => 'klucb', 'horizon' => 'HORIZON']],
        # --- Bayes UCB algorithms
        ['archtype' => 'BayesUCB', 'params' => ['posterior' => 'Beta']],
        # --- AdBandits with different alpha paramters
        ['archtype' => 'AdBandits', 'params' => ['alpha' => '0.1', 'horizon' => 'HORIZON']],
        ['archtype' => 'AdBandits', 'params' => ['alpha' => '0.2', 'horizon' => 'HORIZON']],
        ['archtype' => 'AdBandits', 'params' => ['alpha' => '0.3', 'horizon' => 'HORIZON']],
        ['archtype' => 'AdBandits', 'params' => ['alpha' => '0.4', 'horizon' => 'HORIZON']],
        ['archtype' => 'AdBandits', 'params' => ['alpha' => '0.5', 'horizon' => 'HORIZON']],
        ['archtype' => 'AdBandits', 'params' => ['alpha' => '0.6', 'horizon' => 'HORIZON']],
        ['archtype' => 'AdBandits', 'params' => ['alpha' => '0.7', 'horizon' => 'HORIZON']],
        ['archtype' => 'AdBandits', 'params' => ['alpha' => '0.8', 'horizon' => 'HORIZON']],
        ['archtype' => 'AdBandits', 'params' => ['alpha' => '0.9', 'horizon' => 'HORIZON']],
        # --- Horizon-dependent algorithm ApproximatedFHGittins
        ['archtype' => 'ApproximatedFHGittins', 'params' => ['alpha' => '0.5', 'horizon' => 'max(HORIZON + 100, int(1.05 * HORIZON))']],
        # --- The new OSSB algorithm
        ['archtype' => 'OSSB', 'params' => ['epsilon' => '0.01', 'gamma' => '0.0']],
        # --- The awesome BESA algorithm
        ['archtype' => 'BESA', 'params' => [
            'horizon' => 'HORIZON',
            'minPullsOfEachArm' => '1',
            'randomized_tournament' => 'True',
            'random_subsample' => 'True',
            'non_binary' => 'False',
            'non_recursive' => 'False'
        ]],
        # --- Auto-tuned UCBdagger algorithm
        ['archtype' => 'UCBdagger', 'params' => ['horizon' => 'HORIZON']],
        # --- new UCBoost algorithms
        ['archtype' => 'UCB_bq', 'params' => []],
        ['archtype' => 'UCB_h', 'params' => []],
        ['archtype' => 'UCB_lb', 'params' => []],
        ['archtype' => 'UCBoost_bq_h_lb', 'params' => []],
        ['archtype' => 'UCBoostEpsilon', 'params' => ['epsilon' => 0.1]],
        ['archtype' => 'UCBoostEpsilon', 'params' => ['epsilon' => 0.2]],
        ['archtype' => 'UCBoostEpsilon', 'params' => ['epsilon' => 0.3]],
        ['archtype' => 'UCBoostEpsilon', 'params' => ['epsilon' => 0.4]],
        ['archtype' => 'UCBoostEpsilon', 'params' => ['epsilon' => 0.5]],
        ['archtype' => 'UCBoostEpsilon', 'params' => ['epsilon' => 0.6]],
        ['archtype' => 'UCBoostEpsilon', 'params' => ['epsilon' => 0.7]],
        ['archtype' => 'UCBoostEpsilon', 'params' => ['epsilon' => 0.8]],
        ['archtype' => 'UCBoostEpsilon', 'params' => ['epsilon' => 0.9]],
        # --- other
        ['archtype' => 'AdSwitch', 'params' => ['horizon' => 'HORIZON']],
        ['archtype' => 'LM_DSEE', 'params' => ['nu' => '0.25', 'DeltaMin' => '0.1', 'a' => '1', 'b' => '0.25']]
    ]
];
