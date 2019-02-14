Niezależnie czy korzystałem z Windows-a czy Lubuntu zawsze korzystałem z Pythona 3.x.

## Lubuntu

```
virtualenv -p python3 .
source ./bin/activate
apt-get install python3-tk
apt-get install python3-dev
pip install -r requirements.txt && pip install cython && pip install joblib && pip install h5py && pip install numba
```

```
NOPLOTS=True python main.py configuration_tspol
NOPLOTS=True N=100 python main.py configuration_tspol
NOPLOTS=True N=100 T=5000 python main.py configuration_tspol
```

## Windows

Na Windows nie działają `symlinks` stąd skopiowałem treści plików `with_proba.py`, `kullback.py` oraz `usenumba.py`.

```
virtualenv -p python3 .
Scripts\activate.bat
pip install -r requirements.txt && pip install usejoblib && pip install cython && pip install h5py && pip install numba
```

```
set NOPLOTS=True&& set N=100 && set T=5000 && set N_JOBS=8 && python main.py configuration_tspol
set NOPLOTS=True&& set N=1 && set T=50 && set N_JOBS=1 && python main.py configuration_tspol
```
