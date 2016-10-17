<?php

require('../../../www/preload.php');

/**
 * В СЛУЧАЕ ВЫЛЕТА ПО ПАМЯТИ УМЕНЬШИТЬ $limit
 */

ob_get_clean();

$file = fopen( 'analytics.csv', 'r' );

// первую пропускаю
fgets($file);

$analytics = [];
while( $str = fgets($file) ){
	
	@list( $query, $url ) = explode( ',', $str );
	
	if( !empty( $url ) && !empty( $query ) && !preg_match( '#(not provided|not set)#iu', $query ) ){
		$analytics[$url][] = $query;
	}
}

fclose( $file );

$offset = 0;
$limit = 1000;

while( true ){
	
	echo 'starting offset ' . $offset . "\n";
	
	$goods = ORM::factory('good')->limit($limit)->offset($offset)->find_all()->as_array();
	$offset += $limit;
	
	if( empty( $goods ) ){
		
		break;
	}
	
	foreach( $goods as &$good ){
		
		$result = DB::query(Database::SELECT, "SELECT tag_id FROM z_good_tag WHERE good_id = $good->id")->execute();
		
		$tagIds = [];
		
		while($row = $result->current() ){
		
			$tagIds[$row['tag_id']] = $row['tag_id'];
			$result->next();
		}
		
		$tags = [];
		if( !empty( $tagIds ) ){
			
			$tags = ORM::factory('tag')->where('id', 'in', $tagIds )->find_all()->as_array('id');
			
			foreach( $tags as $tagId => &$tag ){
				
				unset( $tagIds[$tagId] );
			}
			unset( $tag );
		}
		
		// Запускаем перелинковку для карточки, у которой есть ссылка на несуществующую теговую
		// Или где кол-во теговых меньше двух
		if( !empty( $tagIds ) || count( $tags ) < 2 ){
			
			$section = ORM::factory('section', $good->section_id);
			
			$sectionTags = DB::select('id', 'code', 'anchor', 'section_id')
            ->from('z_tag')
            ->where('section_id', '=', $section->id)
            ->where('goods_count', '!=', 0)
            ->order_by('goods_count', 'DESC')
            ->execute()
            ->as_array('id');
			
			$brandTags = DB::select('id', 'code', 'anchor', 'section_id')
            ->from('z_tag')
			->join('z_tag_brand')->on('z_tag.id','=','z_tag_brand.tag_id')
			->where('z_tag_brand.brand_id', '=', $good->brand->id)
            ->where('goods_count', '!=', 0)
            ->order_by('goods_count', 'DESC')
            ->execute()
            ->as_array('id');
			
			$candidateTags = array_merge( array_values( $sectionTags ), array_values( $brandTags ) );

			$targetTags = [];
			foreach( $brandTags as $tagId => &$tag ){
				
				if( $tag['section_id'] == $section->id ){
					
					$targetTags[] = $tag;
				}
			}
			unset( $tag );

			echo 'targets: ' . count( $targetTags ) . "\n";
			
			/* if( !empty( $sectionTags ) ){
				
				$randKeys = [];
				
				while( true ){
					
					$key = rand( 0, count( $sectionTags ) - 1 );
					
					if( !in_array( $key, $randKeys ) ){
						
						$randKeys[] = $key;
					}
					
					if( count( $randKeys ) >= 2 )
						break;
				}

				$_sectionTags = array_values($sectionTags);
				
				for( $i = 0; $i <= 1; $i++ ){

					$selectedTag = $_sectionTags[$randKeys[$i]];
					
					/* if( 0 && !empty( $analytics['/' . $selectedTag['code']] ) ){
						$anchor = $analytics['/' . $selectedTag['code']][rand(0,count( $analytics['/' . $selectedTag['code']] ) - 1)];
					}
					else{ 
						$anchor = $selectedTag['anchor'];
					// }
					
					echo $anchor . "\n";
				}

				// echo $good->section_id . "\n";
				exit;
				
				continue;
			}
			else{
				echo 'tags empty for section ' . $section->id . '; good ' . $good->id . "\n";
			} */
		}
	}
	unset( $good );
}

echo 'all goods finished: ' . $offset . "\n";
