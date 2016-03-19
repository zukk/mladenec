create table good_dups (good_id int unsigned not null, section_id int unsigned not null);

#запросы для теста - генерят категорию Япония в eatmart
#insert into z_section (vitrina, name, sort, code) values ('ogurchik', 'Япония', 8, 888);

#во всех этих категориях есть японские товары
#insert into z_section (id, vitrina, name, translit, sort, code, parent_id)
#  select id + 888, 'ogurchik', name, concat(translit, '-1'), sort, id + 888, 245790 from z_section
#  where id IN (29798, 29293, 91821, 53580, 29494, 28783, 28719, 29585, 28856, 28628, 28836, 29461,
#   175812, 29891, 43574, 64447, 28962, 29138, 29946, 29982, 28903, 91819, 29962
#);

#дубли
#insert into good_dups (good_id, section_id) select id, section_id + 888 from z_good where active = 1 and price > 0 and country_id = 60;