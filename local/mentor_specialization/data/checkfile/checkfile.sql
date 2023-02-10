SELECT f.id                                                     as "id du fichier",
       u.email                                                  as "adresse mél",
       f.filename                                               as "nom du fichier",
       pg_size_pretty(f.filesize::bigint)                       as "taille du fichier",
       COALESCE(cm.course, co.id)                               as "id du cours",
       COALESCE(co.shortname, co2.shortname)                    as "shortname",
       COALESCE(co.fullname, co2.fullname)                      as "fullname",
       cm.section                                               as "section",
       m.name                                                   as "type d'activité",
       cm.id                                                    as "id de l'activité",
       concat('/mod/', m.name, '/view.php?id=', cm.id)          as "url de l'activité",
       TO_CHAR(to_timestamp(f.timecreated)::date, 'DD-MM-YYYY') as "date de dépôt'"
FROM mdl_files f
         FULL JOIN
     mdl_user u ON f.userid = u.id
         FULL JOIN
     mdl_context c ON f.contextid = c.id
         FULL JOIN
     mdl_course_modules cm ON c.instanceid = cm.id
         FULL JOIN
     mdl_course co ON cm.course = co.id
         FULL JOIN
     mdl_modules m ON cm.module = m.id
         FULL JOIN
     mdl_context c2 ON f.contextid = c2.id
         FULL JOIN
     mdl_course co2 ON c2.instanceid = co2.id
WHERE (f.mimetype ~ '^video\/.*$' OR
	 f.filename ~ '^.*\.(mp4|MP4)$|^.*\.(mkv|MKV)$|^.*\.(avi|AVI)$|^.*\.(mov|MOV)$|^.*\.(ogg|OGG)$|^.*\.(vob|VOB)$|^.*\.(wmv|WMV)$')
  AND (c.contextlevel = 70 OR
       c2.contextlevel = 50)
ORDER BY f.filesize DESC