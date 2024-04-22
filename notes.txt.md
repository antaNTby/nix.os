# <!-- notes.txt -->

antaNT64pro@DellWorkBook MINGW64 /d/OSPanel/domains/nix.os (main)
$ git rm --cached GIT.sublime-workspace
rm 'GIT.sublime-workspace'

antaNT64pro@DellWorkBook MINGW64 /d/OSPanel/domains/nix.os (main)
$ git rm --cached GIT.sublime-project
rm 'GIT.sublime-project'


Q: warning: in the working copy of 'GIT.sublime-workspace', LF will be replaced by CRLF the next time Git touches it

git config --global core.autocrlf false

"auto_complete_commit_on_tab": true,
Теперь автозаполнение работает только при нажатии Tab






{
// Кодировка
"fallback_encoding": "Cyrillic (Windows 1251)", // запасная кодирова если (не удалось разобрать основную)
"show_encoding": true, // показывать кодировку в статусбаре

// Не запоминать открытые файлы
"hot_exit": false,
"remember_open_files": false,

"update_check": false, // не проверять обновления
// "trim_trailing_white_space_on_save": true, // при сохранении обрезать пробелы в конце строк справа

// При открытии папок правила неотображения файлов и подпапок
// "folder_exclude_patterns": [".*"], // не отображать папки начинающиеся с точки
// "file_exclude_patterns": [".*"], // не отображать файлы начинающиеся с точки

// Настройки темы
// "theme": "Material-Theme.sublime-theme",
// "color_scheme": "Packages/Material Theme/schemes/Material-Theme.tmTheme",
"theme": "Material-Theme-Palenight.sublime-theme",
"color_scheme": "Packages/Material Theme/schemes/Material-Theme-Palenight.tmTheme",
"always_show_minimap_viewport" : true,
"bold_folder_labels"           : true,
// "font_size": 12,
"font_options"                 : ["gray_antialias", "subpixel_antialias"], // on retina Mac & Windows
"indent_guide_options"         : ["draw_normal", "draw_active"], // highlight active indent
"line_padding_bottom"          : 1,
"line_padding_top"             : 1,
"overlay_scroll_bars"          : "enabled", // автоматически скрывать полосы прокрутки
"word_wrap": false,
// "enable_tab_scrolling": false, // отключить стрелки для прокрутки вкладок

// Сайдбар (боковая панель)
"material_theme_big_fileicons": true, // увеличенные значки файлов
"material_theme_bullet_tree_indicator": true,
"material_theme_compact_sidebar": true, // компактный вид (уменьшен междустрочный интервал)

// Статусбар (нижняя статус-панель)
"material_theme_contrast_mode": true,   // выделение цветом
"material_theme_small_statusbar": true, // компактный вид
}