#(For PayU Ukraine and Russian ONLY)
------

#Универсальная кнопка.
========


#Для установки кнопки требуется только :
-------------
1. Добавить файлы в любое удобное для вас место сайта
2. Откройте в любом редакторе файл payu_button.php
3. Настройте модуль : 

```php
define("__MERCHANT__", "мерчант");  # идентификатор_мерчанта 
define("__SECRETKEY__", "ключ"); 	# Секретный ключ мернчанта
define("__DEBUG__", 1); # Режим отладки. 1 = включен, 0 = включен 

define("__currency__", "UAH");  # Currency of merchant 
define("__language__", "RU");  # Язык платежной системы 

define("__PAYUIMG__", "/payu.jpg");  # Прямая ссылка на картинку
```

4. Добавить следующий код на страницу:
```HTML 
<script src="/payu_button.php?get=all" type="text/javascript"></script>
```
# Обратите внимание, чтобы путь к файлу был правильным
5. Добавьте к любому элементу сайта идентификатор 
например :
```HTML
<img src='./img/test.jpg' id='PayU_paybutton' >
```


-----

# Пример :
----
```HTML
<html>
	<head>
		<script src="./payu_button.php?get=all" type="text/javascript"></script>
		<style>
			.button_payu{padding:5px; background-color:#aeaeae; cursor:pointer; text-align:center;}
		</style>
	</head>
	<body>
		<div id='PayU_paybutton' class='button_payu'> PAY </div>
	</body>
</html>
```