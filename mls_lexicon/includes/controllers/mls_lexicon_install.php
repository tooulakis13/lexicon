<?php
/* 
 *	MLS Lexicon Plugin Installation Function
 *	Prepare SQL queries, install or update Database,
 *	Install plugin options, create user roles, create default page
 *	@call mls_lexicon_db_install() DB installation or update
 *	@call mls_lexicon_create_roles() MLS Lexicon Roles creation
 *	@call mls_lexicon_page() Create default page
 *	@call mls_lexicon_options() Install plugin options
 *	@call mls_demo_data_load()	Install demo data;
 *	@package MLS Lexicon
 */
function mls_lexicon_install()
{
	mls_lexicon_db_install();
	mls_lexicon_create_roles();
	mls_lexicon_page();
	mls_lexicon_options();
}

/*
 * MLS Lexicon Database Installation or Upgrade
 */
function mls_lexicon_db_install() {
		global $wpdb;
		$mls_lexicon_db_version = 1.45; /* Database Version */
		/* Table names definitions */
		define('_mls_lexicon_LANG_MOD', $wpdb->prefix.'mls_lexicon_lang_mod');
		define('_mls_lexicon_COURSE', $wpdb->prefix.'mls_lexicon_course');
		define('_mls_lexicon_COURSE_STUDENT', $wpdb->prefix.'mls_lexicon_course_student');
		define('_mls_lexicon_COURSE_STUDENT_CARD', $wpdb->prefix.'mls_lexicon_course_student_card');
		define('_mls_lexicon_COURSE_AUTHOR', $wpdb->prefix.'mls_lexicon_course_author');
		define('_mls_lexicon_COURSE_CODES', $wpdb->prefix.'mls_lexicon_course_codes');
		define('_mls_lexicon_WORDS', $wpdb->prefix.'mls_lexicon_words');
		define('_mls_lexicon_CODES', $wpdb->prefix.'mls_lexicon_codes');
		define('_mls_lexicon_CATEGORIES', $wpdb->prefix.'mls_lexicon_categories');
		define('_mls_lexicon_CATEGORIES_TRANS', $wpdb->prefix.'mls_lexicon_categories_trans');
		define('_mls_lexicon_CODES_IMG', $wpdb->prefix.'mls_lexicon_codes_img');
		define('_mls_lexicon_CATEGORIES_IMG', $wpdb->prefix.'mls_lexicon_categories_img');
		
		/* Table creation SQL */
		
		$c_sqls = array();
		// mls_lexicon_lang_mod
			$c_sqls[] = "CREATE TABLE "._mls_lexicon_LANG_MOD." (
						id INT unsigned NOT NULL auto_increment PRIMARY KEY,
						lang VARCHAR(30) NOT NULL DEFAULT '',
						level VARCHAR(30) NOT NULL DEFAULT '',
						CONSTRAINT lang_mod UNIQUE (lang, level)
						) DEFAULT CHARSET=utf8, ENGINE = INNODB;";
			// mls_lexicon_course
			$c_sqls[] = "CREATE TABLE "._mls_lexicon_COURSE."(
					id int unsigned NOT NULL auto_increment PRIMARY KEY,
					lang_1 varchar(30) NOT NULL DEFAULT '',
					lang_2 varchar(30) NOT NULL DEFAULT '',		
					level varchar(10) NOT NULL DEFAULT '',
					description varchar(255),
					CONSTRAINT COURSE_U01 UNIQUE (lang_1, lang_2, level),
					CONSTRAINT LANG_MOD_FK01 FOREIGN KEY (lang_1, level) REFERENCES "._mls_lexicon_LANG_MOD." (lang, level)
					ON DELETE CASCADE
				    ON UPDATE CASCADE,
					CONSTRAINT LANG_MOD_FK02 FOREIGN KEY (lang_2, level) REFERENCES "._mls_lexicon_LANG_MOD." (lang, level)
					ON DELETE CASCADE
				    ON UPDATE CASCADE							
					) DEFAULT CHARSET=utf8, ENGINE = INNODB; ";
			// mls_lexicon_course_student
			$c_sqls[] = "CREATE TABLE "._mls_lexicon_COURSE_STUDENT."(
					student_id bigint(20) unsigned NOT NULL DEFAULT 0,
					course_id int unsigned NOT NULL DEFAULT 0,
					state int unsigned NOT NULL DEFAULT 0,
          			PRIMARY KEY (student_id, course_id),
					CONSTRAINT COURSE_STUDENT_FK01 FOREIGN KEY (course_id) REFERENCES "._mls_lexicon_COURSE." (id)
       				ON DELETE CASCADE
				    ON UPDATE CASCADE,
					CONSTRAINT COURSE_STUDENT_FK02 FOREIGN KEY (student_id) REFERENCES ".$wpdb->prefix."users (id)
       				ON DELETE CASCADE
				    ON UPDATE CASCADE	
					) DEFAULT CHARSET=utf8, ENGINE = INNODB; ";
			// mls_lexicon_course_student_card
			$c_sqls[] = "CREATE TABLE "._mls_lexicon_COURSE_STUDENT_CARD."(
					student_id bigint(20) unsigned NOT NULL DEFAULT 0,
					course_id int unsigned NOT NULL DEFAULT 0,
					code varchar(16) NOT NULL DEFAULT '',
					prog_level int unsigned NOT NULL DEFAULT 0,
					CONSTRAINT COURSE_STUDENT_CARD_FK01 FOREIGN KEY (student_id, course_id) REFERENCES "._mls_lexicon_COURSE_STUDENT." (student_id, course_id)
       				ON DELETE CASCADE
				    ON UPDATE CASCADE,
					CONSTRAINT COURSE_STUDENT_CARD_FK02 FOREIGN KEY (course_id) REFERENCES "._mls_lexicon_COURSE." (id)
       				ON DELETE CASCADE
				    ON UPDATE CASCADE			
					) DEFAULT CHARSET=utf8, ENGINE = INNODB; ";
			// mls_lexicon_course_author
			$c_sqls[] = "CREATE TABLE "._mls_lexicon_COURSE_AUTHOR."(
					teacher_id bigint(20) unsigned NOT NULL DEFAULT 0,
					course_id int unsigned NOT NULL DEFAULT 0,									
					PRIMARY KEY (teacher_id, course_id),
					CONSTRAINT COURSE_AUTHOR_FK01 FOREIGN KEY (course_id) REFERENCES "._mls_lexicon_COURSE." (id)
					ON DELETE CASCADE
					ON UPDATE CASCADE			
					) DEFAULT CHARSET=utf8, ENGINE = INNODB; ";
			// mls_lexicon_course_codes
			$c_sqls[] = "CREATE TABLE "._mls_lexicon_COURSE_CODES."(
					course_id int unsigned NOT NULL DEFAULT 0,
					code varchar(16) NOT NULL DEFAULT '',
          			category_code varchar(120),
					PRIMARY KEY (course_id, code),
					CONSTRAINT COURSE_CODE_FK01 FOREIGN KEY (course_id) REFERENCES "._mls_lexicon_COURSE." (id)
					ON DELETE CASCADE
					ON UPDATE CASCADE 			
					) DEFAULT CHARSET=utf8, ENGINE = INNODB; ";
			// mls_lexicon_codes
			$c_sqls[] = "CREATE TABLE "._mls_lexicon_CODES."(
					code varchar(45) NOT NULL PRIMARY KEY,
					notion_type INT NOT NULL,
					class INT NOT NULL,
					subclass INT NOT NULL,
					mgroup INT NOT NULL,
					subgroup INT NOT NULL,
					unit INT NOT NULL,
					theme INT NOT NULL	
					) DEFAULT CHARSET=utf8, ENGINE = INNODB; ";
			// mls_lexicon_code_img
			$c_sqls[] = "CREATE TABLE "._mls_lexicon_CODES_IMG." (
					code varchar(45) NOT NULL PRIMARY KEY,
					image BLOB,
					mimetype VARCHAR(255),
					CONSTRAINT CODES_IMG_FK_01 FOREIGN KEY (code) REFERENCES "._mls_lexicon_CODES." (code)
					ON DELETE CASCADE
					ON UPDATE CASCADE
					) DEFAULT CHARSET=utf8, ENGINE = INNODB; ";
			// mls_lexicon_words
			$c_sqls[] = "CREATE TABLE "._mls_lexicon_WORDS."(
					id int unsigned NOT NULL auto_increment PRIMARY KEY,
					lang_mod_id int unsigned NOT NULL DEFAULT 0,
					code varchar(16) NOT NULL,
					text varchar(30) NOT NULL,
					phrase varchar(120),
          			must_learn int NOT NULL,
					CONSTRAINT WORDS_U01 UNIQUE (lang_mod_id, code),
					CONSTRAINT WORDS_FK01 FOREIGN KEY (lang_mod_id) REFERENCES "._mls_lexicon_LANG_MOD." (id)
					ON DELETE CASCADE
					ON UPDATE CASCADE,
					CONSTRAINT WORDS_FK02 FOREIGN KEY (code) REFERENCES "._mls_lexicon_CODES." (code)
					ON DELETE CASCADE
					ON UPDATE CASCADE
					) DEFAULT CHARSET=utf8, ENGINE = INNODB; ";
			// mls_lexicon_categories
			$c_sqls[] = "CREATE TABLE "._mls_lexicon_CATEGORIES."(
					id int unsigned NOT NULL auto_increment PRIMARY KEY,
					notion_type INT NOT NULL,
					class INT NOT NULL,
					subclass INT NOT NULL,
					mgroup INT NOT NULL,
					subgroup INT NOT NULL,
					CONSTRAINT pk_Lexicon_Cat UNIQUE (notion_type, class, subclass, mgroup, subgroup)
					) DEFAULT CHARSET=utf8, ENGINE = INNODB; ";
			// mls_lexicon_categories_img
			$c_sqls[] = "CREATE TABLE "._mls_lexicon_CATEGORIES_TRANS."(
					id int unsigned NOT NULL auto_increment PRIMARY KEY,
					cat_id INT unsigned NOT NULL,
					lang  VARCHAR(5) NOT NULL,
					name VARCHAR(255) NOT NULL,
					CONSTRAINT CAT_TRANKS_U01 UNIQUE (cat_id, lang),
					CONSTRAINT CAT_TRANS_FK01 FOREIGN KEY (cat_id) REFERENCES "._mls_lexicon_CATEGORIES." (id)
					ON DELETE CASCADE
					ON UPDATE CASCADE	
					) DEFAULT CHARSET=utf8, ENGINE = INNODB; ";
			// mls_lexicon_categories_img
			$c_sqls[] = "CREATE TABLE "._mls_lexicon_CATEGORIES_IMG." (
					id int unsigned NOT NULL PRIMARY KEY,
					image BLOB,
					mimetype VARCHAR(255),
					CONSTRAINT CATEGORIES_IMG_FK_01 FOREIGN KEY (id) REFERENCES "._mls_lexicon_CATEGORIES." (id)
					ON DELETE CASCADE
					ON UPDATE CASCADE
					) DEFAULT CHARSET=utf8, ENGINE = INNODB; ";
			// Course author alteration on wp user delete trigger
				$c_sqls[] = "DROP TRIGGER IF exists mls_lexicon_teacher_delete;";
				$c_sqls[] = "CREATE TRIGGER mls_lexicon_teacher_delete BEFORE DELETE on {$wpdb->prefix}users
							FOR EACH ROW
							BEGIN
							UPDATE {$wpdb->prefix}mls_lexicon_course_author
							SET teacher_id = 
    						(SELECT wp.ID from {$wpdb->prefix}users AS wp 
    						LEFT JOIN {$wpdb->prefix}usermeta AS wpu ON 
     						(wpu.user_id = wp.ID AND wpu.meta_key = 'wp_capabilities'
							AND wpu.meta_value LIKE '%administrator%') 
    						WHERE wp.ID = wpu.user_id) 
    						WHERE teacher_id = OLD.ID;
							END;";	
		// Check for existing DB
		if (get_option("mls_lexicon_db_version")=="") { 
			//No db registered
			//Check for existing DB, drop if found - assuming error in DB, //TODO rethink approach
			if(count($wpdb->get_results("SHOW TABLES LIKE '%mls_lexicon%'")) > 0 ) {
				$d_sql[] = "SET @tables = NULL;";
				$d_sql[] = "SELECT GROUP_CONCAT(table_schema, '.', table_name) 
				INTO @tables FROM information_schema.tables 
  				WHERE table_schema = '{$wpdb->dbname}' AND table_name LIKE BINARY '%mls_lexicon%';";
				$d_sql[] = "SET @tables = CONCAT('DROP TABLE IF EXISTS ', @tables);";
				$d_sql[] = "PREPARE tabdrop FROM @tables;";
				$d_sql[] = "EXECUTE tabdrop;";
				$d_sql[] = "DEALLOCATE PREPARE tabdrop;";
				foreach($d_sql as $sql) {
				$wpdb->query($sql);
				}
				};
			/*
			*	DB Tables SQL
			*/

			$error = false; //Set error flag
			$wpdb->query('START TRANSACTION'); //Begin transaction
			foreach($c_sqls as $sql) { // Iterate through selected queries
				
				if(!$wpdb->query($sql)){
				$error = $wpdb->print_error(); //Capture error
				break;
				};
			}
			if(!$error) {
					$wpdb->query('COMMIT'); // Commit changes and update DB version
				add_option("mls_lexicon_db_version", $mls_lexicon_db_version);
				} else {
					 $wpdb->query('ROLLBACK'); //Rollback changes
					echo $error;
				}
			} elseif(get_option("mls_lexicon_db_version") < $mls_lexicon_db_version) {
				// DB version outdated
			$sqls = Array();
			// Install Foreign Key deletion procedure
			$wpdb->query("DROP PROCEDURE IF EXISTS dropForeignKeysFromTable;");
			$wpdb->query("
					create procedure dropForeignKeysFromTable(IN param_table_schema varchar(255), IN param_table_name varchar(255))
					begin
   					declare done int default FALSE;
    				declare dropCommand varchar(255);
    				declare dropCur cursor for 
        			select concat('alter table ',table_schema,'.',table_name,' DROP INDEX ',constraint_name, ';') 
        			from information_schema.table_constraints
        			where constraint_type='FOREIGN KEY' or constraint_type='UNIQUE'
            		and table_name = param_table_name
            		and table_schema = param_table_schema;
					declare continue handler for not found set done = true;
					open dropCur;
					read_loop: loop
        			fetch dropCur into dropCommand;
        			if done then
            			leave read_loop;
       				end if;
					set @sdropCommand = dropCommand;
					prepare dropClientUpdateKeyStmt from @sdropCommand;
					execute dropClientUpdateKeyStmt;
					deallocate prepare dropClientUpdateKeyStmt;
    				end loop;
					close dropCur;
					end;
					");
			//mls_lexicon_lang_mod
			if(count($wpdb->get_results("SHOW TABLES LIKE '%mls_lexicon_lang_mod'")) > 0) { // Alter if found
			
			$sql_lm = "ALTER TABLE "._mls_lexicon_LANG_MOD;
			if(count($wpdb->get_results("SHOW COLUMNS FROM `"._mls_lexicon_LANG_MOD."` LIKE 'id'")) > 0) { $sql_lm .= "MODIFY id INT unsigned NOT NULL auto_increment,"; } 
			else {$sql_lm .="ADD id INT unsigned NOT NULL auto_increment,";};
			if(count($wpdb->get_results("SHOW COLUMNS FROM `"._mls_lexicon_LANG_MOD."` LIKE 'lang'")) > 0) { $sql_lm .= "MODIFY lang VARCHAR(30) NOT NULL DEFAULT '',";} 
			else {$sql_lm .="ADD lang VARCHAR(30) NOT NULL DEFAULT '',";};
			if(count($wpdb->get_results("SHOW COLUMNS FROM `"._mls_lexicon_LANG_MOD."` LIKE 'level'")) > 0) { $sql_lm .= "MODIFY level VARCHAR(30) NOT NULL DEFAULT '',";} 
			else {$sql_lm .="ADD level VARCHAR(30) NOT NULL DEFAULT '',";};
			
			$sql_lm .= "DROP PRIMARY KEY,
						ADD PRIMARY KEY (lang, level)";
			$sqls[] = $sql_lm;
			} else {  // Add if not found
				$sqls[] = $c_sqls[0];
			}
			//mls_lexicon_course
			if(count($wpdb->get_results("SHOW TABLES LIKE '"._mls_lexicon_COURSE."'")) > 0) { // Alter if found
			$wpdb->query("call dropForeignKeysFromTable('".$wpdb->dbname."', '"._mls_lexicon_COURSE."');");	
			$sql_lm = "ALTER TABLE "._mls_lexicon_COURSE;
			if(count($wpdb->get_results("SHOW COLUMNS FROM `"._mls_lexicon_COURSE."` LIKE 'id'")) > 0) { $sql_lm .= "MODIFY id INT unsigned NOT NULL auto_increment,"; } 
			else {$sql_lm .="ADD id INT unsigned NOT NULL auto_increment,";};
			if(count($wpdb->get_results("SHOW COLUMNS FROM `"._mls_lexicon_COURSE."` LIKE 'lang_1'")) > 0) { $sql_lm .= "MODIFY lang_1 varchar(30) NOT NULL DEFAULT '',"; } 
			else {$sql_lm .="ADD lang_1 varchar(30) NOT NULL DEFAULT '',";};
			if(count($wpdb->get_results("SHOW COLUMNS FROM `"._mls_lexicon_COURSE."` LIKE 'lang_2'")) > 0) { $sql_lm .= "MODIFY lang_2 varchar(30) NOT NULL DEFAULT '',"; } 
			else {$sql_lm .="ADD lang_2 varchar(30) NOT NULL DEFAULT '',";};
			if(count($wpdb->get_results("SHOW COLUMNS FROM `"._mls_lexicon_COURSE."` LIKE 'level'")) > 0) { $sql_lm .= "MODIFY level varchar(10) NOT NULL DEFAULT '',"; } 
			else {$sql_lm .="ADD level varchar(10) NOT NULL DEFAULT '',";};
			if(count($wpdb->get_results("SHOW COLUMNS FROM `"._mls_lexicon_COURSE."` LIKE 'description'")) > 0) { $sql_lm .= "MODIFY description varchar(255),"; } 
			else {$sql_lm .="ADD description varchar(255),";};
			$sqls[] = $sql_lm;
			$sqls[] = "ALTER TABLE "._mls_lexicon_COURSE." 
					DROP PRIMARY KEY,
					ADD PRIMARY KEY (id)";
			$sqls[] = "ALTER TABLE "._mls_lexicon_COURSE."
					ADD CONSTRAINT LANG_MOD_FK01 FOREIGN KEY (lang_1, level) REFERENCES "._mls_lexicon_LANG_MOD." (lang, level)
					ON DELETE CASCADE
				    ON UPDATE CASCADE,					
					ADD CONSTRAINT LANG_MOD_FK02 FOREIGN KEY (lang_2, level) REFERENCES "._mls_lexicon_LANG_MOD." (lang, level)
					ON DELETE CASCADE
				    ON UPDATE CASCADE";
			} else {  // Add if not found
				$sqls[] = $c_sqls[1];
			}
			//mls_lexicon_course_student
			if(count($wpdb->get_results("SHOW TABLES LIKE '"._mls_lexicon_COURSE_STUDENT."'")) > 0) { // Alter if found
			$wpdb->query("call dropForeignKeysFromTable('".$wpdb->dbname."', '"._mls_lexicon_COURSE_STUDENT."');");	
			$sql_lm = "ALTER TABLE "._mls_lexicon_COURSE_STUDENT;
			if(count($wpdb->get_results("SHOW COLUMNS FROM `"._mls_lexicon_COURSE_STUDENT."` LIKE 'student_id'")) > 0) { $sql_lm .= "MODIFY student_id bigint(20) unsigned NOT NULL DEFAULT 0,"; } else {$sql_lm .="ADD student_id bigint(20) unsigned NOT NULL DEFAULT 0,";};
			if(count($wpdb->get_results("SHOW COLUMNS FROM `"._mls_lexicon_COURSE_STUDENT."` LIKE 'course_id'")) > 0) { $sql_lm .= "MODIFY course_id int unsigned NOT NULL DEFAULT 0,"; }
			else {$sql_lm .="ADD course_id int unsigned NOT NULL DEFAULT 0,";};
			if(count($wpdb->get_results("SHOW COLUMNS FROM `"._mls_lexicon_COURSE_STUDENT."` LIKE 'state'")) > 0) { $sql_lm .= "MODIFY state int unsigned NOT NULL DEFAULT 0,"; } 
			else {$sql_lm .="ADD state int unsigned NOT NULL DEFAULT 0,";};
			$sqls[] = $sql_lm;
			$sqls[] = "ALTER TABLE "._mls_lexicon_COURSE_STUDENT."
					DROP PRIMARY KEY,
          			ADD PRIMARY KEY (student_id, course_id)";
			$sqls[] = "ALTER TABLE "._mls_lexicon_COURSE_STUDENT."
					ADD CONSTRAINT COURSE_STUDENT_FK01 FOREIGN KEY (course_id) REFERENCES "._mls_lexicon_COURSE." (id)
       				ON DELETE CASCADE
				    ON UPDATE CASCADE,
					ADD CONSTRAINT COURSE_STUDENT_FK02 FOREIGN KEY (student_id) REFERENCES ".$wpdb->prefix."users (ID)
       				ON DELETE CASCADE
				    ON UPDATE CASCADE";
			} else {  // Add if not found
				$sqls[] = $c_sqls[2];
			}
			// mls_lexicon_course_student_card
			if(count($wpdb->get_results("SHOW TABLES LIKE '"._mls_lexicon_COURSE_STUDENT_CARD."'")) > 0) { // Alter if found
			$wpdb->query("call dropForeignKeysFromTable('".$wpdb->dbname."', '"._mls_lexicon_COURSE_STUDENT_CARD."');");
			$sqls[] = "ALTER TABLE "._mls_lexicon_COURSE_STUDENT_CARD."
					MODIFY student_id bigint(20) unsigned NOT NULL DEFAULT 0,
					MODIFY course_id int unsigned NOT NULL DEFAULT 0,
					MODIFY code varchar(16) NOT NULL DEFAULT '',
					MODIFY prog_level int unsigned NOT NULL DEFAULT 0";
			$sqls[] = "ALTER TABLE "._mls_lexicon_COURSE_STUDENT_CARD."
					ADD CONSTRAINT COURSE_STUDENT_CARD_FK01 FOREIGN KEY (student_id, course_id) REFERENCES "._mls_lexicon_COURSE_STUDENT." (student_id, course_id)
       				ON DELETE CASCADE
				    ON UPDATE CASCADE,
					ADD CONSTRAINT COURSE_STUDENT_CARD_FK02 FOREIGN KEY (course_id) REFERENCES "._mls_lexicon_COURSE." (id)
       				ON DELETE CASCADE
				    ON UPDATE CASCADE";
			} else {  // Add if not found
				$sqls[] = $c_sqls[3];
			}
			// mls_lexicon_course_author
			if(count($wpdb->get_results("SHOW TABLES LIKE '"._mls_lexicon_COURSE_AUTHOR."'")) > 0) { // Alter if found
			$wpdb->query("call dropForeignKeysFromTable('".$wpdb->dbname."', '"._mls_lexicon_COURSE_AUTHOR."');");
			$sqls[] = "ALTER TABLE "._mls_lexicon_COURSE_AUTHOR."
					MODIFY teacher_id bigint(20) unsigned NOT NULL DEFAULT 0,
					MODIFY course_id int unsigned NOT NULL DEFAULT 0,	
					DROP PRIMARY KEY,								
					ADD PRIMARY KEY (teacher_id, course_id)";
			$sqls[] = "ALTER TABLE "._mls_lexicon_COURSE_AUTHOR."
					ADD CONSTRAINT COURSE_AUTHOR_FK01 FOREIGN KEY (course_id) REFERENCES "._mls_lexicon_COURSE." (id)
					ON DELETE CASCADE
					ON UPDATE CASCADE";
			} else {  // Add if not found
				$sqls[] = $c_sqls[4];
			}
			// mls_lexicon_course_codes
			if(count($wpdb->get_results("SHOW TABLES LIKE '"._mls_lexicon_COURSE_CODES."'")) > 0) { // Alter if found			
			$wpdb->query("call dropForeignKeysFromTable('".$wpdb->dbname."', '"._mls_lexicon_COURSE_CODES."');");
			$sql_lm = "ALTER TABLE "._mls_lexicon_COURSE_CODES;
			if(count($wpdb->get_results("SHOW COLUMNS FROM `"._mls_lexicon_COURSE_CODES."` LIKE 'course_id'")) > 0) { $sql_lm .= "MODIFY course_id int unsigned NOT NULL DEFAULT 0,"; } 
			else {$sql_lm .="ADD course_id int unsigned NOT NULL DEFAULT 0,";};
			if(count($wpdb->get_results("SHOW COLUMNS FROM `"._mls_lexicon_COURSE_CODES."` LIKE 'code'")) > 0) { $sql_lm .= "MODIFY code varchar(16) NOT NULL DEFAULT '',"; } 
			else {$sql_lm .="ADD code varchar(16) NOT NULL DEFAULT '',";};
			if(count($wpdb->get_results("SHOW COLUMNS FROM `"._mls_lexicon_COURSE_CODES."` LIKE 'context'")) > 0) { $sql_lm .= "MODIFY context varchar(120),"; } 
			else {$sql_lm .="ADD context varchar(120),";};
			
			
          			
			$sql_lm .= "DROP PRIMARY KEY,
					ADD PRIMARY KEY (course_id, code)";
			$sqls[] = $sql_lm;
			$sqls[] = "ALTER TABLE "._mls_lexicon_COURSE_CODES."
					ADD CONSTRAINT COURSE_CODE_FK01 FOREIGN KEY (course_id) REFERENCES "._mls_lexicon_COURSE." (id)
					ON DELETE CASCADE
					ON UPDATE CASCADE";
			} else {  // Add if not found
				$sqls[] = $c_sqls[5];
			}
			// mls_lexicon_codes	
			if(count($wpdb->get_results("SHOW TABLES LIKE '"._mls_lexicon_CODES."'")) > 0) { // Alter if found			
			$wpdb->query("call dropForeignKeysFromTable('".$wpdb->dbname."', '"._mls_lexicon_CODES."');");
			
			$sql_lm = "ALTER TABLE "._mls_lexicon_CODES;
			if(count($wpdb->get_results("SHOW COLUMNS FROM `"._mls_lexicon_CODES."` LIKE 'code'")) > 0) { $sql_lm .= "MODIFY code varchar(45) NOT NULL,"; } 
			else {$sql_lm .="ADD code varchar(45) NOT NULL,";};
			if(count($wpdb->get_results("SHOW COLUMNS FROM `"._mls_lexicon_CODES."` LIKE 'notion_type'")) > 0) { $sql_lm .= "notion_type INT NOT NULL,"; } 
			else {$sql_lm .="ADD notion_type INT NOT NULL,";};
			if(count($wpdb->get_results("SHOW COLUMNS FROM `"._mls_lexicon_CODES."` LIKE 'class'")) > 0) { $sql_lm .= "MODIFY class INT NOT NULL,"; } 
			else {$sql_lm .="ADD class INT NOT NULL,";};
			if(count($wpdb->get_results("SHOW COLUMNS FROM `"._mls_lexicon_CODES."` LIKE 'subclass'")) > 0) { $sql_lm .= "MODIFY subclass INT NOT NULL,"; } 
			else {$sql_lm .="ADD subclass INT NOT NULL,";};
			if(count($wpdb->get_results("SHOW COLUMNS FROM `"._mls_lexicon_CODES."` LIKE 'mgroup'")) > 0) { $sql_lm .= "MODIFY mgroup INT NOT NULL,"; } 
			else {$sql_lm .="ADD mgroup INT NOT NULL,";};
			if(count($wpdb->get_results("SHOW COLUMNS FROM `"._mls_lexicon_CODES."` LIKE 'subgroup'")) > 0) { $sql_lm .= "MODIFY subgroup INT NOT NULL,"; } 
			else {$sql_lm .="ADD subgroup INT NOT NULL,";};
			if(count($wpdb->get_results("SHOW COLUMNS FROM `"._mls_lexicon_CODES."` LIKE 'unit'")) > 0) { $sql_lm .= "MODIFY unit INT NOT NULL,,"; } 
			else {$sql_lm .="ADD unit INT NOT NULL,";};
			if(count($wpdb->get_results("SHOW COLUMNS FROM `"._mls_lexicon_CODES."` LIKE 'theme'")) > 0) { $sql_lm .= "MODIFY theme INT NOT NULL,"; } 
			else {$sql_lm .="ADD theme INT NOT NULL,";};
			if(count($wpdb->get_results("SHOW COLUMNS FROM `"._mls_lexicon_CODES."` LIKE 'image'")) > 0) { $sql_lm .= "MODIFY image BLOB,"; } 
			else {$sql_lm .="ADD image BLOB,";};
			if(count($wpdb->get_results("SHOW COLUMNS FROM `"._mls_lexicon_CODES."` LIKE 'mimetype'")) > 0) { $sql_lm .= "MODIFY mimetype VARCHAR(255),"; } 
			else {$sql_lm .="ADD mimetype VARCHAR(255),";};
			
			
			
			$sql_lm .= "DROP PRIMARY KEY,
					ADD PRIMARY KEY (code)";
					
				$sqls[] = $sql_l;
			} else {  // Add if not found
				$sqls[] = $c_sqls[6];
			}
			// mls_lexicon_words
			if(count($wpdb->get_results("SHOW TABLES LIKE '"._mls_lexicon_WORDS."'")) > 0) { // Alter if found
			$wpdb->query("call dropForeignKeysFromTable('".$wpdb->dbname."', '"._mls_lexicon_WORDS."');");
			$sqls[] = "ALTER TABLE "._mls_lexicon_WORDS."
					MODIFY id int unsigned NOT NULL auto_increment,
					MODIFY lang_mod_id int unsigned NOT NULL DEFAULT 0,
					MODIFY code varchar(16) NOT NULL DEFAULT '',
					MODIFY text varchar(30) NOT NULL DEFAULT '',
					MODIFY phrase varchar(120),
	          		MODIFY must_learn int NOT NULL,
					DROP PRIMARY KEY,
				  	ADD PRIMARY KEY (id)";
			$sqls[] = "ALTER TABLE "._mls_lexicon_WORDS."
					ADD CONSTRAINT WORDS_FK01 FOREIGN KEY (lang_mod_id) REFERENCES "._mls_lexicon_LANG_MOD." (id)
					ON DELETE CASCADE
					ON UPDATE CASCADE,
					ADD CONSTRAINT WORDS_FK02 FOREIGN KEY (code) REFERENCES "._mls_lexicon_CODES." (code)
					ON DELETE CASCADE
					ON UPDATE CASCADE
					";
					} else {  // Add if not found
				$sqls[] = $c_sqls[8];
			}
			// mls_lexicon_categories
			if(count($wpdb->get_results("SHOW TABLES LIKE '"._mls_lexicon_CATEGORIES."'")) > 0) { // Alter if found
			$wpdb->query("call dropForeignKeysFromTable('".$wpdb->dbname."', '"._mls_lexicon_CATEGORIES."');");
			$sqls[] = "ALTER TABLE "._mls_lexicon_CATEGORIES."
					MODIFY id int unsigned NOT NULL auto_increment,
					MODIFY notion_type INT NOT NULL,
					MODIFY class INT NOT NULL,
					MODIFY subclass INT NOT NULL,
					MODIFY mgroup INT NOT NULL,
					MODIFY subgroup INT NOT NULL,
					MODIFY image BLOB,
					MODIFY mimetype VARCHAR(255),
					DROP DROP INDEX pk_Lexicon_Cat,
					ADD CONSTRAINT pk_Lexicon_Cat UNIQUE (id, notion_type, class, subclass, mgroup, subgroup)	
					 ";
					} else {  // Add if not found
				$sqls[] = $c_sqls[9];
			}
			// mls_lexicon_categories_trans
			if(count($wpdb->get_results("SHOW TABLES LIKE '"._mls_lexicon_CATEGORIES_TRANS."'")) > 0) { // Alter if found
			$wpdb->query("call dropForeignKeysFromTable('".$wpdb->dbname."', '"._mls_lexicon_CATEGORIES_TRANS."');");
			$sqls[] = "ALTER TABLE "._mls_lexicon_CATEGORIES_TRANS."
					MODIFY id int unsigned NOT NULL auto_increment,
					MODIFY cat_id int unsigned NOT NULL,
					MODIFY lang  VARCHAR(5) NOT NULL,
					MODIFY name VARCHAR(255) NOT NULL,
					DROP PRIMARY KEY,
					ADD PRIMARY KEY (id)		
					 ";
			$sqls[] = "ALTER TABLE "._mls_lexicon_CATEGORIES_TRANS."
					ADD CONSTRAINT CAT_TRANS_FK01 FOREIGN KEY (cat_id) REFERENCES "._mls_lexicon_CATEGORIES." (id)
					ON DELETE CASCADE
					ON UPDATE CASCADE
					";
					} else {  // Add if not found
				$sqls[] = $c_sqls[10];
			}
			//Trigger:
			$sqls[] = $c_sqls[11];
			//Error flag
			$error = false;
			$wpdb->query('START TRANSACTION');
			foreach($sqls as $sql) {
				
				if(!$wpdb->query($sql)){
				$error = $wpdb->print_error();
				break;
				};
			}
			if(!$error) {
					$wpdb->query('COMMIT');
					// Update DB version
				update_option("mls_lexicon_db_version", $mls_lexicon_db_version);
				} else {
					 $wpdb->query('ROLLBACK');
					echo $error;
				}
				
				
				
		}
}
/*
 *	MLS Lexicon Roles Creation
 */
function mls_lexicon_create_roles(){
		
			
  $res = add_role('mls_lexicon_student', 'Lexicon Student', array(
  'read' => true, 
  'mls_lexicon_course_enroll' => true, 
  'mls_lexicon_course_study' => true));
		       
  if(!$res) 
  {
    // Assing student privileges
    $role = get_role('mls_lexicon_student');
    if(!$role->has_cap('mls_lexicon_course_enroll')) $role->add_cap('mls_lexicon_course_enroll');
	if(!$role->has_cap('mls_lexicon_course_study')) $role->add_cap('mls_lexicon_course_study');
  } 
	
  $res = add_role('mls_lexicon_teacher', 'Lexicon Teacher', array(
  'read' => true, 
  'mls_lexicon_course_enroll' => true, 
  'mls_lexicon_course_study' => true, 
  'mls_lexicon_edit_course_custom_authorial' => true, 
  'mls_lexicon_create_course_custom' => true));
		     
  if(!$res) 
  {
    // Assign teacher privileges
    $role = get_role('mls_lexicon_teacher');
   if(!$role->has_cap('mls_lexicon_course_enroll')) $role->add_cap('mls_lexicon_course_enroll');
	if(!$role->has_cap('mls_lexicon_course_study')) $role->add_cap('mls_lexicon_course_study');
	if(!$role->has_cap('mls_lexicon_edit_course_custom_authorial')) $role->add_cap('mls_lexicon_edit_course_custom_authorial');
	if(!$role->has_cap('mls_lexicon_create_course_custom')) $role->add_cap('mls_lexicon_create_course_custom');
  }
  
  $res = add_role('mls_lexicon_editor', 'Lexicon Editor', array(
  'read' => true, 
  'mls_lexicon_course_enroll' => true, 
  'mls_lexicon_course_study' => true, 
  'mls_lexicon_edit_course_custom' => true, 
  'mls_lexicon_create_course_custom' => true, 
  'mls_lexicon_edit_course' => true, 
  'mls_lexicon_create_course' => true));
		     
  if(!$res) 
  {
    // Assign editor privileges
    $role = get_role('mls_lexicon_editor');
    if(!$role->has_cap('mls_lexicon_course_enroll')) $role->add_cap('mls_lexicon_course_enroll');
	if(!$role->has_cap('mls_lexicon_course_study')) $role->add_cap('mls_lexicon_course_study');
	if(!$role->has_cap('mls_lexicon_edit_course_custom')) $role->add_cap('mls_lexicon_edit_course_custom');
	if(!$role->has_cap('mls_lexicon_create_course_custom')) $role->add_cap('mls_lexicon_create_course_custom');
	if(!$role->has_cap('mls_lexicon_edit_course')) $role->add_cap('mls_lexicon_edit_course');
	if(!$role->has_cap('mls_lexicon_create_course')) $role->add_cap('mls_lexicon_create_course');
  }
  
  $res = add_role('mls_lexicon_admin', 'Lexicon Admin', array(
  'read' => true, 
  'mls_lexicon_course_enroll' => true, 
  'mls_lexicon_course_study' => true, 
  'mls_lexicon_edit_course_custom' => true, 
  'mls_lexicon_create_course_custom' => true, 
  'mls_lexicon_edit_course' => true, 
  'mls_lexicon_create_course' => true, 
  'mls_lexicon_management' => true));
		     
  if(!$res) 
  {
    // Assign mls lexicon admin privileges
    $role = get_role('mls_lexicon_admin');
    if(!$role->has_cap('mls_lexicon_course_enroll')) $role->add_cap('mls_lexicon_course_enroll');
	if(!$role->has_cap('mls_lexicon_course_study')) $role->add_cap('mls_lexicon_course_study');
	if(!$role->has_cap('mls_lexicon_edit_course_custom')) $role->add_cap('mls_lexicon_edit_course_custom');
	if(!$role->has_cap('mls_lexicon_create_course_custom')) $role->add_cap('mls_lexicon_create_course_custom');
	if(!$role->has_cap('mls_lexicon_edit_course')) $role->add_cap('mls_lexicon_edit_course');
	if(!$role->has_cap('mls_lexicon_create_course')) $role->add_cap('mls_lexicon_create_course');
	if(!$role->has_cap('mls_lexicon_management')) $role->add_cap('mls_lexicon_management');
  }
	
  // Assign administrator privileges
  $role = get_role('administrator');
  if(!$role->has_cap('mls_lexicon_course_enroll')) $role->add_cap('mls_lexicon_course_enroll');
	if(!$role->has_cap('mls_lexicon_course_study')) $role->add_cap('mls_lexicon_course_study');
	if(!$role->has_cap('mls_lexicon_edit_course_custom')) $role->add_cap('mls_lexicon_edit_course_custom');
	if(!$role->has_cap('mls_lexicon_create_course_custom')) $role->add_cap('mls_lexicon_create_course_custom');
	if(!$role->has_cap('mls_lexicon_edit_course')) $role->add_cap('mls_lexicon_edit_course');
	if(!$role->has_cap('mls_lexicon_create_course')) $role->add_cap('mls_lexicon_create_course');
	if(!$role->has_cap('mls_lexicon_management')) $role->add_cap('mls_lexicon_management');
 
}
/*
 *	Create default MLS Lexicon page
 */	
function mls_lexicon_page(){			
	$obj = get_page_by_title('MLS Lexicon');
	function doit_page() {
				$post = array ();
   				$post['post_title'] = 'MLS Lexicon';
 				$post['post_type'] = 'page';
			    $post['post_content'] = '[mls_lexicon]';
			    $post['post_status'] = 'publish';
			    $post['post_author'] = 1;
			    $post['comment_status'] = 'closed';
			    $post['ping_satuts'] = 'closed';
			    $post['page_template'] = 'front-page.php';
			    wp_insert_post($post);			
			}
		if( !$obj == null) // Check if page already exists
 			{
				if($obj->post_status == 'trash') {		
		
    			$obj->post_status = 'publish';								
				}
			} else {
				doit_page();
			}
			
}
/*
 *	Insert or update default lexicon options
 */
function mls_lexicon_options(){
		if (get_option("mls_lexicon_install") != (1 or 0)) { update_option("mls_lexicon_install", '1'); }
		if (get_option("mls_lexicon_student_size") == "") { update_option("mls_lexicon_student_size", '16'); }	
		if (get_option("mls_lexicon_order_flashcards") == "") { update_option("mls_lexicon_order_flashcards", 'basic');}
		if (get_option("mls_lexicon_presentation_flashcards") == "") { update_option("mls_lexicon_presentation_flashcards", 'buttons');}
		if (get_option("mls_lexicon_perorm_course") == "") { update_option("mls_lexicon_perorm_course", 'categories');	}
		if (get_option("mls_lexicon_display_statistics") != (1 or 0)) { update_option("mls_lexicon_display_statistics", '1');	}
		if (get_option("mls_lexicon_algorythm") != (1 or 0 or 2)) { update_option("mls_lexicon_algorythm", '1');	}
		if (get_option("mls_lexicon_custom_list_pages_default") == "") { update_option("mls_lexicon_custom_list_pages_default", '8');	}
		if (get_option("mls_lexicon_cleanup_db") != (1 or 0)) { update_option("mls_lexicon_cleanup_db", '1');	}
		if (get_option("mls_lexicon_clear_data_deactive")!=(1 or 0)) {	update_option("mls_lexicon_clear_data_deactive", '0');	}
		if (get_option("mls_lexicon_delete_page")!=(1 or 0)) { update_option("mls_lexicon_delete_page", '1'); }
		
}
?>