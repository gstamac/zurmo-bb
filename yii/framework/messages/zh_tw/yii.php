<?php
/**
 * Message translations.
 *
 * This file is automatically generated by 'yiic message' command.
 * It contains the localizable messages extracted from source code.
 * You may modify this file by translating the extracted messages.
 *
 * Each array element represents the translation (value) of a message (key).
 * If the value is empty, the message is considered as not translated.
 * Messages that no longer need translation will have their translations
 * enclosed between a pair of '@@' marks.
 *
 * NOTE, this file must be saved in UTF-8 encoding.
 */
return array (
  '"{path}" is not a valid directory.' => '"{path}" 不是一個合法的目錄.',
  'Active Record requires a "db" CDbConnection application component.' => 'Active Record 需要一個名為 "db" 的 CDbConnection 應用程式元件.',
  'Active record "{class}" has an invalid configuration for relation "{relation}". It must specify the relation type, the related active record class and the foreign key.' => 'Active record "{class}" 對於關聯 "{relation}" 有一個無效的組態設定. 必須給定關聯種類, 相關的 active record class 以及 foreign key.',
  'Active record "{class}" is trying to select an invalid column "{column}". Note, the column must exist in the table or be an expression with alias.' => 'Active record "{class}" 正嘗試選擇一個無效的欄位 "{column}". 注意, 該欄位必須存在於 table 中或者是一個具別名的 expression.',
  'Alias "{alias}" is invalid. Make sure it points to an existing directory or file.' => '別名 "{alias}" 是無效的. 請確定它指向一個已存在的目錄或檔案.',
  'Application base path "{path}" is not a valid directory.' => '應用程式基準路徑 "{path}" 是無效的目錄.',
  'Application runtime path "{path}" is not valid. Please make sure it is a directory writable by the Web server process.' => '應用程式執行時的路徑 "{path}" 是無效的. 請確定它是一個可被 Web server process 寫入資料的目錄.',
  'Authorization item "{item}" has already been assigned to user "{user}".' => '授權項目 "{item}" 已經被指派給使用者 "{user}".',
  'CApcCache requires PHP apc extension to be loaded.' => 'CApcCache 要求 PHP apc extension 必須先被載入.',
  'CAssetManager.basePath "{path}" is invalid. Please make sure the directory exists and is writable by the Web server process.' => 'CAssetManager.basePath "{path}" 是無效的. 請確定它是一個可被 Web server process 寫入資料的目錄.',
  'CCacheHttpSession.cacheID is invalid. Please make sure "{id}" refers to a valid cache application component.' => 'CCacheHttpSession.cacheID 是無效的. 請確定 "{id}" 參照到一個有效的快取應用程式元件.',
  'CCaptchaValidator.action "{id}" is invalid. Unable to find such an action in the current controller.' => 'CCaptchaValidator.action "{id}" 是無效的. 無法在目前的控制器中找到此一動作.',
  'CDbAuthManager.connectionID "{id}" is invalid. Please make sure it refers to the ID of a CDbConnection application component.' => 'CDbAuthManager.connectionID "{id}" 是無效的. 請確定它參照到一個 CDbConnection 應用程式元件的 ID.',
  'CDbCache.connectionID "{id}" is invalid. Please make sure it refers to the ID of a CDbConnection application component.' => 'CDbCache.connectionID "{id}" 是無效的. 請確定它參照到一個 CDbConnection 應用程式元件的 ID.',
  'CDbCacheDependency.sql cannot be empty.' => 'CDbCacheDependency.sql 不能是空的.',
  'CDbCommand failed to execute the SQL statement: {error}' => 'CDbCommand 無法執行 SQL 陳述: {error}',
  'CDbCommand failed to prepare the SQL statement: {error}' => 'CDbCommand 無法準備 SQL 陳述: {error}',
  'CDbConnection does not support reading schema for {driver} database.' => 'CDbConnection 不支援對 {driver} 資料庫 schema 的讀取.',
  'CDbConnection failed to open the DB connection: {error}' => 'CDbConnection 無法開啟資料庫連線: {error}',
  'CDbConnection is inactive and cannot perform any DB operations.' => 'CDbConnection 狀態為未啟用, 無法進行任何資料庫動作.',
  'CDbConnection.connectionString cannot be empty.' => 'CDbConnection.connectionString 不能是空的.',
  'CDbDataReader cannot rewind. It is a forward-only reader.' => 'CDbDataReader 無法倒回, 只允許向前讀取.',
  'CDbHttpSession.connectionID "{id}" is invalid. Please make sure it refers to the ID of a CDbConnection application component.' => 'CDbHttpSession.connectionID "{id}" 是無效的. 請確定它參照到一個 CDbConnection 應用程式元件的 ID.',
  'CDbLogRoute requires database table "{table}" to store log messages.' => 'CDbLogRoute 要求資料庫 table "{table}" 儲存日誌訊息.',
  'CDbLogRoute.connectionID "{id}" does not point to a valid CDbConnection application component.' => 'CDbLogRoute.connectionID "{id}" 未指向一個有效的 CDbConnection 應用程式元件.',
  'CDbMessageSource.connectionID is invalid. Please make sure "{id}" refers to a valid database application component.' => 'CDbMessageSource.connectionID 是無效的. 請確定 "{id}" 參照到一個有效的資料庫應用程式原件.',
  'CDbTransaction is inactive and cannot perform commit or roll back operations.' => 'CDbTransaction 狀態為未啟用, 無法進行 commit 或 roll back 動作.',
  'CDirectoryCacheDependency.directory cannot be empty.' => 'CDirectoryCacheDependency.directory 不能是空的.',
  'CFileCacheDependency.fileName cannot be empty.' => 'CFileCacheDependency.fileName 不能是空的.',
  'CFileLogRoute.logPath "{path}" does not point to a valid directory. Make sure the directory exists and is writable by the Web server process.' => 'CFileLogRoute.logPath "{path}" does not point to a valid directory. 請確定目錄存在並且允許 Web server process 寫入.',
  'CFilterChain can only take objects implementing the IFilter interface.' => 'CFilterChain 只能取得有實作 IFilter 介面的物件.',
  'CFlexWidget.baseUrl cannot be empty.' => 'CFlexWidget.baseUrl 不能是空的.',
  'CFlexWidget.name cannot be empty.' => 'CFlexWidget.name 不能是空的.',
  'CGlobalStateCacheDependency.stateName cannot be empty.' => 'CGlobalStateCacheDependency.stateName 不能是空的.',
  'CHttpCookieCollection can only hold CHttpCookie objects.' => 'CHttpCookieCollection 只能持有 CHttpCookie 物件.',
  'CHttpRequest is unable to determine the entry script URL.' => 'CHttpRequest 不能確認入口腳本的 URL.',
  'CHttpRequest is unable to determine the path info of the request.' => 'CHttpRequest 不能確認請求的路徑資訊.',
  'CHttpRequest is unable to determine the request URI.' => 'CHttpRequest 不能確認請求的 URI.',
  'CHttpSession.cookieMode can only be "none", "allow" or "only".' => 'CHttpSession.cookieMode 只能是 "none", "allow" 或 "only".',
  'CHttpSession.gcProbability "{value}" is invalid. It must be an integer between 0 and 100.' => 'CHttpSession.gcProbability "{value}" 是無效的. 它必須是介於 0 與 100之間的整數.',
  'CHttpSession.savePath "{path}" is not a valid directory.' => 'CHttpSession.savePath "{path}" 不是一個有效的目錄.',
  'CMemCache requires PHP memcache extension to be loaded.' => 'CMemCache 要求 PHP memcache 插件必須先被載入.',
  'CMemCache server configuration must be an array.' => 'CMemCache 伺服器組態設定必須是一個陣列.',
  'CMemCache server configuration must have "host" value.' => 'CMemCache 伺服器組態設定必須有 "host" 的值.',
  'CMultiFileUpload.name is required.' => 'CMultiFileUpload.name 是必要的.',
  'CProfileLogRoute found a mismatching code block "{token}". Make sure the calls to Yii::beginProfile() and Yii::endProfile() be properly nested.' => 'CProfileLogRoute found a mismatching code block "{token}". 請確定對於 Yii::beginProfile() 與 Yii::endProfile() 的呼叫是適當地巢狀套疊的.',
  'CProfileLogRoute.report "{report}" is invalid. Valid values include "summary" and "callstack".' => 'CProfileLogRoute.report "{report}" 是無效的. 有效值包含 "summary" 與 "callstack".',
  'CSecurityManager requires PHP mcrypt extension to be loaded in order to use data encryption feature.' => 'CSecurityManager 要求 PHP mcrypt extension 必須先被載入以便使用資料加密功能.',
  'CSecurityManager.encryptionKey cannot be empty.' => 'CSecurityManager.encryptionKey 不能是空的.',
  'CSecurityManager.validation must be either "MD5" or "SHA1".' => 'CSecurityManager.validation 必須是 "MD5" 或 "SHA1".',
  'CSecurityManager.validationKey cannot be empty.' => 'CSecurityManager.validationKey 不能是空的.',
  'CTypedList<{type}> can only hold objects of {type} class.' => 'CTypedList<{type}> 只能持有 {type} 類別的物件.',
  'CUrlManager.UrlFormat must be either "path" or "get".' => 'CUrlManager.UrlFormat 必須是 "path" 或 "get".',
  'CXCache requires PHP XCache extension to be loaded.' => 'CXCache 要求 PHP XCache 插件必須先被載入.',
  'Cache table "{tableName}" does not exist.' => 'Cache table "{tableName}" 不存在.',
  'Cannot add "{child}" as a child of "{name}". A loop has been detected.' => '無法新增 "{child}" 成為 "{name}" 的子項. 偵測到有迴圈產生.',
  'Cannot add "{child}" as a child of "{parent}". A loop has been detected.' => '無法新增 "{child}" 成為 "{parent}" 的子項. 偵測到有迴圈產生.',
  'Cannot add "{name}" as a child of itself.' => '無法新增 "{name}" 成為它自己的子項.',
  'Cannot add an item of type "{child}" to an item of type "{parent}".' => '無法新增一個種類為 "{child}" 的項目到一個種類為 "{parent}" 的項目.',
  'Either "{parent}" or "{child}" does not exist.' => '"{parent}" 或 "{child}" 不存在.',
  'Error: Table "{table}" does not have a primary key.' => '錯誤: Table "{table}" 沒有定義主鍵.',
  'Error: Table "{table}" has a composite primary key which is not supported by crud command.' => '錯誤: Table "{table}" 有一個不被 crud 命令所支援的合成主鍵.',
  'Event "{class}.{event}" is attached with an invalid handler "{handler}".' => '事件 "{class}.{event}" 附加了一個無效的 handler "{handler}".',
  'Event "{class}.{event}" is not defined.' => '事件 "{class}.{event}" 未定義.',
  'Failed to write the uploaded file "{file}" to disk.' => '無法將已上傳的檔案 "{file}" 寫入磁碟.',
  'File upload was stopped by extension.' => '檔案上傳被插件所停止.',
  'Filter "{filter}" is invalid. Controller "{class}" does have the filter method "filter{filter}".' => '篩選器 "{filter}" 是無效的. 控制器 "{class}" 沒有名為 "filter{filter}" 的篩選器方法.',
  'Get a new code' => '取得一組新代碼',
  'Invalid MO file revision: {revision}.' => '無效的 MO 檔案修訂: {revision}.',
  'Invalid MO file: {file} (magic: {magic}).' => '無效的 MO 檔案: {file} (magic: {magic}).',
  'Invalid enumerable value "{value}". Please make sure it is among ({enum}).' => '無效的 enumerable 值 "{value}". 請確定它在 ({enum}) 之中.',
  'List data must be an array or an object implementing Traversable.' => '表列資料必須是一個陣列或是有實作 Traversable 的一個物件.',
  'List index "{index}" is out of bound.' => '表列索引 "{index}" 超出範圍.',
  'Login Required' => '需要先登入系統',
  'Map data must be an array or an object implementing Traversable.' => '對照表資料必須是一個陣列或一個實作 Traversable 的物件.',
  'Missing the temporary folder to store the uploaded file "{file}".' => '缺乏暫存目錄來儲存已上傳的檔案 "{file}".',
  'No columns are being updated for table "{table}".' => 'table "{table}" 沒有任何欄位將被更新.',
  'No counter columns are being updated for table "{table}".' => 'table "{table}" 沒有任何計數器欄位將被更新.',
  'Object configuration must be an array containing a "class" element.' => '物件組態設定必須是內含有一個 "class" 元素的一個陣列.',
  'Please fix the following input errors:' => '請更正下列輸入錯誤:',
  'Property "{class}.{property}" is not defined.' => '屬性 "{class}.{property}" 未被定義.',
  'Property "{class}.{property}" is read only.' => '屬性 "{class}.{property}" 是唯讀的.',
  'Queue data must be an array or an object implementing Traversable.' => '佇列資料必須是一個陣列或一個實作 Traversable 的物件.',
  'Relation "{name}" is not defined in active record class "{class}".' => '關聯 "{name}" 未被定義在 active record class "{class}" 中.',
  'Stack data must be an array or an object implementing Traversable.' => '堆疊資料必須是一個陣列或一個實作 Traversable 的物件.',
  'Table "{table}" does not have a column named "{column}".' => 'Table "{table}" 沒有名為 "{column}" 的欄位.',
  'Table "{table}" does not have a primary key defined.' => 'Table "{table}" 沒有定義主鍵.',
  'The "filter" property must be specified with a valid callback.' => '屬性 "filter" 必須以一個有效的 callback 指明.',
  'The "pattern" property must be specified with a valid regular expression.' => '屬性 "pattern" 必須以一個有效的 regular expression 指明.',
  'The "view" property is required.' => '需要 "view" 屬性',
  'The CSRF token could not be verified.' => 'CSRF token 無法被驗證.',
  'The URL pattern "{pattern}" for route "{route}" is not a valid regular expression.' => 'route "{route}" 中的 URL 樣式 "{pattern}" 不是有效的 regular expression.',
  'The active record cannot be deleted because it is new.' => 'active record 由於是新的, 無法被刪除.',
  'The active record cannot be inserted to database because it is not new.' => 'active record 由於不是新的, 無法被新增到資料庫.',
  'The active record cannot be updated because it is new.' => 'active record 由於是新的, 無法被更新.',
  'The asset "{asset}" to be pulished does not exist.' => '欲發佈的 asset "{asset}" 不存在.',
  'The column "{column}" is not a foreign key in table "{table}".' => '欄位 "{column}" 並不是 table "{table}" 中的一個 foreign key.',
  'The command path "{path}" is not a valid directory.' => '命令路徑 "{path}" 不是一個有效的目錄.',
  'The controller path "{path}" is not a valid directory.' => '控制器路徑 "{path}" 不是一個有效的目錄.',
  'The file "{file}" cannot be uploaded. Only files with these extensions are allowed: {extensions}.' => '檔案 "{file}" 無法被上傳. 只有附檔名如下的檔案是被允許的: {extensions}.',
  'The file "{file}" is too large. Its size cannot exceed {limit} bytes.' => '檔案 "{file}" 太大. 檔案大小不能超過 {limit} 位元組.',
  'The file "{file}" is too small. Its size cannot be smaller than {limit} bytes.' => '檔案 "{file}" 太小. 檔案大小不能少於 {limit} 位元組.',
  'The file "{file}" was only partially uploaded.' => '檔案 "{file}" 上傳不完全.',
  'The first element in a filter configuration must be the filter class.' => '篩選器組態設定中的第一個元素必須是篩選器類別.',
  'The item "{name}" does not exist.' => '項目 "{name}" 不存在.',
  'The item "{parent}" already has a child "{child}".' => '項目 "{parent}" 已有子項目 "{child}".',
  'The layout path "{path}" is not a valid directory.' => '佈局路徑 "{path}" 不是一個有效的目錄.',
  'The list is read only.' => '表列是唯讀的.',
  'The map is read only.' => '對照表是唯讀的.',
  'The pattern for 12 hour format must be "h" or "hh".' => '代表12小時制的樣式必須是 "h" 或 "hh".',
  'The pattern for 24 hour format must be "H" or "HH".' => '代表24小時制的樣式必須是 "H" 或 "HH".',
  'The pattern for AM/PM marker must be "a".' => '代表 AM/PM 標記的樣式必須是 "a".',
  'The pattern for day in month must be "F".' => '代表以月數取代天數的樣式必須是 "F".',
  'The pattern for day in year must be "D", "DD" or "DDD".' => '代表該年的第幾天的樣式必須是 "D", "DD" 或 "DDD".',
  'The pattern for day of the month must be "d" or "dd".' => '代表該月的日子的樣式必須是 "d" 或 "dd".',
  'The pattern for day of the week must be "E", "EE", "EEE", "EEEE" or "EEEEE".' => '代表該星期的第幾天的樣式必須是 "E", "EE", "EEE", "EEEE" 或 "EEEEE".',
  'The pattern for era must be "G", "GG", "GGG", "GGGG" or "GGGGG".' => '代表年代的樣式必須是 "G", "GG", "GGG", "GGGG" 或 "GGGGG".',
  'The pattern for hour in AM/PM must be "K" or "KK".' => '代表時(AM/PM格式)的樣式必須是 "K" 或 "KK".',
  'The pattern for hour in day must be "k" or "kk".' => '代表該天第幾小時的樣式必須是 "k" 或 "kk".',
  'The pattern for minutes must be "m" or "mm".' => '代表分鐘的樣式必須是 "m" 或 "mm".',
  'The pattern for month must be "M", "MM", "MMM", or "MMMM".' => '代表月份的樣式必須是 "M", "MM", "MMM", 或 "MMMM".',
  'The pattern for seconds must be "s" or "ss".' => '代表秒的樣式必須是 "s" 或 "ss".',
  'The pattern for time zone must be "z" or "v".' => '代表時區的樣式必須是 "z" 或 "v".',
  'The pattern for week in month must be "W".' => '代表以月數取代星期數的樣式必須是 "W".',
  'The pattern for week in year must be "w".' => '代表以年數取代星期數的樣式必須是 "w".',
  'The queue is empty.' => '佇列狀態為空.',
  'The relation "{relation}" in active record class "{class}" is not specified correctly: the join table "{joinTable}" given in the foreign key cannot be found in the database.' => 'active record class "{class}" 中的關聯 "{relation}" 未被正確指明: 資料庫中無法找到 foreign key 中所給的 join table "{joinTable}".',
  'The relation "{relation}" in active record class "{class}" is specified with an incomplete foreign key. The foreign key must consist of columns referencing both joining tables.' => 'active record class "{class}" 中的關聯 "{relation}" 有一個不完整的 foreign key. foreign key 必須是參照 joining tables 中的欄位所構成.',
  'The relation "{relation}" in active record class "{class}" is specified with an invalid foreign key "{key}". The foreign key does not point to either joining table.' => 'active record class "{class}" 中的關聯 "{relation}" 有一個無效的 foreign key "{key}". foreign key 未指到任一個 joining table.',
  'The relation "{relation}" in active record class "{class}" is specified with an invalid foreign key. The format of the foreign key must be "joinTable(fk1,fk2,...)".' => 'active record class "{class}" 中的關聯 "{relation}" 有一個無效的 foreign key. foreign key 的格式必須是 "joinTable(fk1,fk2,...)".',
  'The requested controller "{controller}" does not exist.' => '請求的控制器 "{controller}" 不存在.',
  'The requested view "{name}" is not found.' => '請求的 view "{name}" 未找到.',
  'The stack is empty.' => '堆疊狀態為空.',
  'The system is unable to find the requested action "{action}".' => '系統無法找到請求的 "{action}" 動作.',
  'The system view path "{path}" is not a valid directory.' => '系統 view 路徑 "{path}" 不是一個有效的目錄.',
  'The table "{table}" for active record class "{class}" cannot be found in the database.' => '資料庫中無法找到 active record class "{class}" 對應的 table "{table}".',
  'The value for the primary key "{key}" is not supplied when querying the table "{table}".' => '查詢 table "{table}" 時未提供 primary key "{key}" 的值.',
  'The verification code is incorrect.' => '驗證碼不正確.',
  'The view path "{path}" is not a valid directory.' => 'view 路徑 "{path}" 不是一個有效的目錄.',
  'Theme directory "{directory}" does not exist.' => 'Theme 目錄 "{directory}" 不存在.',
  'This content requires the <a href="http://www.adobe.com/go/getflash/">Adobe Flash Player</a>.' => '內容需有 <a href="http://www.adobe.com/go/getflash/">Adobe Flash Player</a>.',
  'Unable to add an item whose name is the same as an existing item.' => '無法新增與已存在項目名稱相同的新項目.',
  'Unable to change the item name. The name "{name}" is already used by another item.' => '無法變更項目名稱. 名稱 "{name}" 已被其它項目使用.',
  'Unable to create application state file "{file}". Make sure the directory containing the file exists and is writable by the Web server process.' => '無法產生應用程式狀態檔案 "{file}". 請確認存放此檔案的目錄存在並且允許 Web server process 寫入.',
  'Unable to find the decorator view "{view}".' => '無法找到 decorator view "{view}".',
  'Unable to find the list item.' => '無法找到表列項目.',
  'Unable to lock file "{file}" for reading.' => '無法封鎖檔案 "{file}" 進行讀取.',
  'Unable to lock file "{file}" for writing.' => '無法封鎖檔案 "{file}" 進行寫入.',
  'Unable to read file "{file}".' => '無法讀取檔案 "{file}".',
  'Unable to replay the action "{object}.{method}". The method does not exist.' => '無法再次重演 "{object}.{method}" 動作. 這個方法不存在.',
  'Unable to write file "{file}".' => '無法寫入檔案 "{file}".',
  'Unknown authorization item "{name}".' => '未知的授權項目 "{name}".',
  'Unrecognized locale "{locale}".' => '無法辨識的地區設定 "{locale}".',
  'View file "{file}" does not exist.' => '名為 "{file}" 的 View 檔不存在.',
  'Yii application can only be created once.' => 'Yii 應用程式只能被產生一次.',
  'You are not authorized to perform this action.' => '您未被授權執行這個動作',
  'Your request is not valid.' => '您的請求無效',
  '{attribute} "{value}" has already been taken.' => '{attribute} "{value}" 已被取用.',
  '{attribute} cannot be blank.' => '{attribute} 不可為空白.',
  '{attribute} is invalid.' => '{attribute} 無效.',
  '{attribute} is not a valid URL.' => '{attribute} 不是有效的 URL.',
  '{attribute} is not a valid email address.' => '{attribute} 不是有效的電子郵件地址.',
  '{attribute} is not in the list.' => '{attribute} 不在表列之中.',
  '{attribute} is of the wrong length (should be {length} characters).' => '{attribute} 長度錯誤 (應為 {length} 字元).',
  '{attribute} is too big (maximum is {max}).' => '{attribute} 數值太大 (最大值為 {max}).',
  '{attribute} is too long (maximum is {max} characters).' => '{attribute} 太長 (最大值為 {max} 字元).',
  '{attribute} is too short (minimum is {min} characters).' => '{attribute} 太短 (最小值為 {min} 字元).',
  '{attribute} is too small (minimum is {min}).' => '{attribute} 數值太小 (最小值為 {min}).',
  '{attribute} must be a number.' => '{attribute} 必須為數字.',
  '{attribute} must be an integer.' => '{attribute} 必須為整數.',
  '{attribute} must be repeated exactly.' => '{attribute} 必須被重覆.',
  '{attribute} must be {type}.' => '{attribute} 必須為 {type}.',
  '{className} does not support add() functionality.' => '{className} 不支援 add() 功能.',
  '{className} does not support delete() functionality.' => '{className} 不支援 delete() 功能.',
  '{className} does not support flush() functionality.' => '{className} 不支援 flush() 功能.',
  '{className} does not support get() functionality.' => '{className} 不支援 get() 功能.',
  '{className} does not support set() functionality.' => '{className} 不支援 set() 功能.',
  '{class} does not have attribute "{name}".' => '{class} 中沒有名為 "{name}" 的屬性.',
  '{class} does not have relation "{name}".' => '{class} 中沒有名為 "{name}" 的關聯.',
  '{class} does not support fetching all table names.' => '{class} 不支援擷取所有 table 名稱.',
  '{class} has an invalid validation rule. The rule must specify attributes to be validated and the validator name.' => '{class} 有一個無效的確認規則. 規則必須指明要被確認的屬性以及確認器名稱.',
  '{class} must specify "model" and "attribute" or "name" property values.' => '{class} 必須給定 "model" 與 "attribute" 或 "name" 屬性值.',
  '{class}.allowAutoLogin must be set true in order to use cookie-based authentication.' => '{class}.allowAutoLogin 必須設為 true 才能使用 cookie-based 認證.',
  '{class}::authenticate() must be implemented.' => '{class}::authenticate() 必須被實作.',
  '{controller} cannot find the requested view "{view}".' => '{controller} 無法找到請求的 "{view}" view.',
  '{controller} contains improperly nested widget tags in its view "{view}". A {widget} widget does not have an endWidget() call.' => '{controller} 在它的 view "{view}" 中含有未被適當巢狀套疊的 widget 標籤. {widget} widget 中沒有呼叫 endWidget().',
  '{controller} has an extra endWidget({id}) call in its view.' => '{controller} 在它的 view 中有一個額外的 endWidget({id}) 呼叫.',
  '{widget} cannot find the view "{view}".' => '{widget} 無法找到這個 view "{view}".',
);
