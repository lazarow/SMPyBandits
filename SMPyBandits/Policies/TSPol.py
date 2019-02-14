import random
import math

# with binomial interval
class TSPol(object):
    def __init__(self, nbArms, alpha=0.02, z=1.96, *args, **kwargs):
        self.nbArms = nbArms
        self.alpha = alpha
        self.z = z
        self.scoreboard = [[1 for i in range(nbArms)] for j in range(nbArms)]
        self.rewards = [0 for i in range(nbArms)]
        self.pulls = [0 for i in range(nbArms)]
        self.defeated = [False for i in range(nbArms)]
        self.t = 0
        random.seed()

    def __str__(self):
        return "TSPol(alpha=" + str(self.alpha) + ",z=" + str(self.z) + ")"

    def startGame(self):
        pass

    def getReward(self, arm, reward):
        #print("arm: " + str(arm))
        #print("reward: " + str(reward))
        self.rewards[arm] += reward
        self.pulls[arm] += 1

    def choice(self):
        # przywracanie pokonane o najwyżej średniej
        mean = None
        arm = None
        if self.t >= self.nbArms:
            for i in range(self.nbArms):
                if mean is None or mean < self.rewards[i] / self.pulls[i]:
                    # normal approximation interval
                    #mean = self.rewards[i] / self.pulls[i] + self.z / self.pulls[i] * math.sqrt(self.rewards[i] * (self.pulls[i] - self.rewards[i]) / self.pulls[i])
                    # Wilson score interval
                    z2 = self.z * self.z
                    mean = (self.rewards[i] + z2 / 2) / (self.pulls[i] + z2) + self.z / (self.pulls[i] + z2) * math.sqrt((self.pulls[i] - self.rewards[i]) / self.pulls[i] + z2 / 4)
                    arm = i
            if self.defeated[arm]:
                self.defeated[arm] = False
        # aktualizacja iteracji
        self.t += 1
        # lista kandydatów
        candidates = []
        for i in range(self.nbArms):
            if self.pulls[i] == 0:
                candidates = [i]
                break
            if self.defeated[i]:
                continue
            candidates.append(i)
        if len(candidates) == 1:
            return candidates[0]
        else:
            random.shuffle(candidates)
            a = candidates[0]
            b = candidates[1]
            r = self.duelFunction(a, b)
            self.scoreboard[a][b] += r
            self.scoreboard[b][a] += 1 - r
            n = self.scoreboard[a][b] + self.scoreboard[b][a]
            Y = n / 2
            p = self.scoreboard[a][b] / n
            t = 2 * self.pmf(Y, n, p)
            if t <= self.alpha:
                if self.scoreboard[a][b] > self.scoreboard[b][a]:
                    self.defeated[b] = True
                else:
                    self.defeated[a] = True
            #print("Candidates: " + str(a) + ", " + str(b))
            #print(self.scoreboard)
            #print(self.rewards)
            #print(self.pulls)
            #print(self.defeated)
            return a if r == 1 else b

    def duelFunction(self, a, b):
        n = self.pulls[a] + self.pulls[b]
        va = self.rewards[a] / self.pulls[a] + math.sqrt(2 * math.log(n) / self.pulls[a]) + 0.00000001 * random.random()
        vb = self.rewards[b] / self.pulls[b] + math.sqrt(2 * math.log(n) / self.pulls[b]) + 0.00000001 * random.random()
        return 1 if va >= vb else 0

    def handleCollision(self, arm):
        pass

    def binom(self, x, n):
        s = 1.0
        for i in range(0, math.floor(x)):
            s = (s * (n - i)) / (i + 1.0)
        return s

    def pmf(self, x, n, pi):
        return self.binom(x, n) * math.pow(pi, x) * math.pow(1.0 - pi, n - x)

