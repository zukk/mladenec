<?
   $string = '/pages/';
   
   // ��������� ����� �� ������� ����� 
   if($this->NavPageNomer != 1) $pervpage = '[ <a href="/">������</a> ] '; 
   elseif($this->NavPageNomer == 1) $pervpage = ' ';
   
   // ��������� ����� �� ������� ������ 
   if($this->NavPageNomer != $this->NavPageCount) $nextpage = ' [ <a href="'.$string.$this->nEndPage.'/">���������</a> ]'; 
   elseif($this->NavPageNomer == $this->NavPageCount) $nextpage = ' ';
   
   // ������� ��� ��������� ������� � ����� �����, ���� ��� ���� 
   if($this->NavPageNomer - 2 > 0) $page2left = ' ... <a href="'.$string.($this->NavPageNomer - 2).'/">'.($this->NavPageNomer - 2).'</a> '; 
   if($this->NavPageNomer - 1 > 0) $page1left = '<a href="'.$string.($this->NavPageNomer - 1).'/">'.($this->NavPageNomer - 1).'</a> '; 
   
   if($this->NavPageNomer + 2 <= $this->NavPageCount) $page2right = ' <a href="'.$string.($this->NavPageNomer + 2).'/">'.($this->NavPageNomer + 2).'</a> ...'; 
   if($this->NavPageNomer + 1 <= $this->NavPageCount) $page1right = ' <a href="'.$string.($this->NavPageNomer + 1).'/">'.($this->NavPageNomer + 1).'</a>';
   
   // "������" ���������
   $nav = '<div id="page_nav">';
      $nav .= '<div class="first_page">'.$pervpage.'</div>';
         $nav .= '<div class="page">'.$page2left.$page1left.'<span class="active">'.$this->NavPageNomer.'</span>'.$page1right.$page2right.'</div>';
      $nav .= '<div class="last_page">'.$nextpage.'</div>';
   $nav .= '</div>';
   
   echo $nav;
?>