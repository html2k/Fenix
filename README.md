Fenix
=====


###v 1.1.2

######Исправления:
* Фикс ошибок связанных с добавлением новых объектов
* Фикс файлового менеджера для ckeditor`a

---


###v 1.1.1

######Добавлено:
* Файловый менеджер для ckeditor

---


###v 1.1.0

######Добавлено:
* Добавлен поиск в структуре для таблиц
* Добавлена возможность удаление строк таблци в структуре

######Исправления:
* Исправлена работы с классами и контекстом, добавлен общий класс синглтон Fx
* Исправлена работа поиска в шапке
* Исправлены контролы dropdown, popup, select (пока не изменены по проекту)

---

###v 1.0.8

######Исправления:
* Выпилил лишний мусор с репозитория

---

###v 1.0.7

######Исправления:
* Исправлены проблемы со списками / чекбоксами / и радиобатонами

---

###v 1.0.6 (refactoring)

######Исправления:
* Less: исправлена работа с препроцессором

######Улучшения:
* Добавлен глобальный класс Fx

---

###v 1.0.5 (small-work)

######Исправления:
* Ошибки в классе Action
* Убрали лишний отступ в css

######Улучшения:
* Добавлен метод getParent для обращения к родительскому элементу в базе данных


---

###v 1.0.4 (prod-bug)

######Исправления:
* Исправлена работа роутинга, для дополнительных get запросов


---


###v 1.0.3 (refactoring)

######Улучшения:
* **LESS::compiler** - добавлен компилятор less файлов
* **Action** - переведены все экшены на методы
* **Главная** - выведена информация по версиям php/mysql и доступное место на диске
* Поправилен урл в индексном файле для Twig шаблонизатора
* Исправлена работа роутинга
* **Templating** - добавлен метод getParent


---


###v 1.0.2 (ckeditor-setting)

######Улучшения:
* **Раздел "Настройки"** - Добавлен вывод настроект для визуального редактора, отрефакторены внутренние шаблоны раздела, изменилась логика работы


---


###v 1.0.1 (fix-bug)

######Улучшения:
* **IO::path** - добавлен метод для работы с путями в файловой системе

######Исправления:
* **Action::object** - при сохранении затирались старые данные во всей таблице
* **CKEditor** - добавлен плагин **nofollow** для выставления атрибутов rel у ссылок ведущих на внешние ресурсы. Отключен плагин **backup** и **stat**. Добавлен плагин **insertpre**


---

