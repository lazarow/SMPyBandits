Niezależnie czy korzystałem z Windows-a czy Lubuntu zawsze korzystałem z Pythona 3.x.

## Lubuntu

```
virtualenv -p python3 .
source ./bin/activate
pip install -r requirements.txt
apt-get install python3-tk
apt-get install python3-dev
pip install cython
pip install joblib
pip install h5py
pip install numba
```

```
NOPLOTS=True python main.py configuration_tspol
NOPLOTS=True N=100 python main.py configuration_tspol
NOPLOTS=True N=100 T=5000 python main.py configuration_tspol
```

## Windows

todo.
