<?php
	/**
 * @author Gasper Kozak
 * @copyright 2007-2011

    This file is part of WideImage.
		
    WideImage is free software; you can redistribute it and/or modify
    it under the terms of the GNU Lesser General Public License as published by
    the Free Software Foundation; either version 2.1 of the License, or
    (at your option) any later version.
		
    WideImage is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU Lesser General Public License for more details.
		
    You should have received a copy of the GNU Lesser General Public License
    along with WideImage; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA

    * @package WideImage
  **/
	
	/**
	 * PS font support class
	 * 
	 * @package WideImage
	 */
	class WideImage_Font_PS
	{
		public $size;
		public $color;
		public $handle;
		
		function __construct($file, $size, $color, $bgcolor = null)
		{
			$this->handle = imagepsloadfont($file);
			$this->size = $size;
			$this->color = $color;
			if ($bgcolor === null)
				$this->bgcolor = $color;
			else
				$this->color = $color;
		}
		
		function writeText($image, $x, $y, $text, $angle = 0)
		{
			if ($image->isTrueColor())
				$image->alphaBlending(true);
			
			imagepstext($image->getHandle(), $text, $this->handle, $this->size, $this->color, $this->bgcolor, $x, $y, 0, 0, $angle, 4);
		}
		
		function __destruct()
		{
			imagepsfreefont($this->handle);
			$this->handle = null;
		}
	}
