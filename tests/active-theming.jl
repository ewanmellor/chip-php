#|

Active-Theming Sawfish Module.  Version 1.1.

Copyright (c) 2004 Ewan Mellor <sawfish@ewanmellor.org.uk>.
Copyright (c) 2004 Andrew Rice <andy@andyrice.net>.
All rights reserved.

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

  The above copyright notice and this permission notice shall be included in
  all copies or substantial portions of the Software.

  THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
  IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
  FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT.  IN NO EVENT SHALL THE
  AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
  LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING 
  FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS
  IN THE SOFTWARE.


Active-Theming varies the colour of window frames depending upon

  the host from which the window comes,
  whether root is logged on at the window,
  whether the window is focussed,
  the window's title,
  the window's group,
  the window's position,
  the window's stickiness, or
  the workspace on which the window is placed.

This can be used to convey useful information, or simply to make your desktop
more colourful.

Colours may either be varied using the RGB or the HSV colour models.  Any
variation method from the list above may vary along any of the axes, so you
could change the hue depending upon the host, the saturation if root is logged
in, and the value when the window is focussed, for example.  Alternatively,
you could have windows becoming more red depending upon the workspace and
heading towards white when the window is focussed by varying both green and
blue axes when the window is focussed.


This module needs support from your current theme.  In the Sawfish
distribution, the mono and simple themes have this support.

Install this file by placing it inside your ~/.sawfish/lisp/ directory, and
then place the following lines into your ~/.sawfish/rc:

(require 'active-theming)
(active-theming-initialise)

This module may be configured using the Sawfish Configurator (sawfish-ui);
its options will appear in the Appearance -> Active-Theming group.  Make sure
you are using a supporting theme too!

This module depends upon the hsv (version 1.2+) and string2 (version 1.6+)
modules, available from

http://www.ewanmellor.org.uk/sawfish.html


The colour model is set using the variable active-theming:model.  Then, each
colour axis is varied using "methods".  When the RGB colour model is
being used, the primary-method affects the red axis, the secondary-method
affects the green axis, and the tertiary-method affects the blue axis.  When
the HSV model is in use, the primary-, secondary-, and tertiary-methods affect
the hue, saturation, and value axes respectively.  Each method variable may be
one of (focus, group, host, position, root, sticky, title, workspace).  These
are explained below.

Each method takes parameters from the variables active-theming:base-color and
active-theming:far-color.  These colours define in some way the range on the
appropriate axis over which the method will apply.  The base colour is used in
the "normal" case (window not focussed, root not logged in, or whatever) and
the far colour is used in the alternative situation.

Some methods use a hash to generate the value required.  This results in a
random value along the appropriate axis; the far colour is ignored.

Focus Method
------------

The appropriate component from the base colour if the window is not focussed,
or from the far colour if it is.

Group Method
------------

A hash of the window's group ID.

Host Method
-----------

The appropriate component from the base colour if the host is
active-theming:base-host, or a hash of the hostname otherwise.

active-theming:base-host defaults to the machine on which you are running
Sawfish.

The actual host of the window is determined either from the window's title,
as explained in the Window Title User and Host Information section below, or
from the X property WM_CLIENT_MACHINE.  This means that for terminals you need
to set the title as explained below, and for remote X windows everything
should just work.

Position Method
---------------

The appropriate component from the base colour if the position is
active-theming:base-position, or a hash of the position otherwise.

Using this method to adjust hue results in a lovely Spectrum-style loading
effect when dragging windows!

Root Method
-----------

The appropriate component from the base colour if not logged in as root
through the current terminal, or the far colour if so.

Whether root is logged in is determined from the window's title as explained
in the Window Title User and Host Information section below.

Sticky Method
-------------

The appropriate component from the base colour if the window is not sticky,
or the far colour if so.

Title Method
------------

The appropriate component from the base colour if the title is
active-theming:base-title, or a hash of the title otherwise.

Workspace Method
----------------

The appropriate component from the base colour if the current workspace is
named as specified by active-theming:base-workspace, or a hash of the reverse
of the workspace name otherwise.  The workspace name is either that given by
the Sawfish variable workspace-names, or "Workspace X" where X is the
workspace number otherwise.  The reverse of the name is used because this
gives better behaviour when no workspaces are explicitly named.


Window Title User and Host Information
--------------------------------------

The root and host methods use the window title to give them the information
required.

In the default configuration, the window title must contain "user@host:",
where user and host give the required information.  This can be generated when
using the bash shell by setting PS1 to contain something like
'\[\033]0;\u@\h: \w\007\]'.  This escape sequence sets the title of the window
to look like "user@host: /current/path".

I actually use

if [ "$UID" != "0" ] && [ "$HOSTNAME" = "punt" ]
then
  PS1='\W \$ \[\033]0;\w\007\]'
else
  PS1='\W \$ \[\033]0;\u@\h: \w\007\]'
fi

(punt is my local machine).  This sets the title as above when logged in to
remote machines, sets it only to show the full current path when working
locally, and in all cases sets the main bash prompt to be the innermost
directory of the current path, followed by a $ when logged on normally, and a
# when logged in as root.  This interacts perfectly with Active-Theming, and
keeps my terminals uncluttered yet informative.

You need PS1 to be set in the .bashrc of all the systems to which you log in.


The title format is actually determined by the active-theming:user-regexp and
active-theming:host-regexp.  These can be changed to match your preferred
window title layout.



Ignored Titles
--------------

When the window title changes, sometimes that change is ignored.  This happens
when the title matches active-theming:ignored-titles-regexp.  In the default
setting, this means that if you are using the program 'screen' with its
hardstatus option enabled, then transient messages from screen will not affect
the window's colours.


Developing This Module
======================

If you wish to reload this module during development, you should call
(active-theming-deinitialise) first.  This removes the hooks installed by
active-theming-initialise; failure to do this may make behaviour glitchy.

Since hooks are used to indicate when window settings have changed, manual
changes to Active-Theming settings will not immediately take effect.  Use the
active-theming-refresh-all method to fix this.

This module uses the American spelling of "color" in all function and variable
names, despite both authors being English.  Sawfish uses "color" itself, so we
have followed that for consistency.  The non-technical documentation uses
British spellings throughout, because we are stubborn.


Theme Interface
---------------

For each managed window this module sets two window properties:
frame-active-color and frame-inactive-color.  These two properties may be read
by the active theme and used to set appropriate colours in the window
decoration.  The Simple theme in the Sawfish distribution
(themes/simple/theme.jl) contains an excellent example showing how to achieve
this whilst falling back to standard ways of setting the colour if the
Active-Theming module is not installed.


|#

(define-structure active-theming
  (export active-theming:base-color
          active-theming:far-color

          active-theming:model

          active-theming:primary-method
          active-theming:secondary-method
          active-theming:tertiary-method

          active-theming:primary-hash-all
          active-theming:secondary-hash-all
          active-theming:tertiary-hash-all

          active-theming:base-host
          active-theming:base-position
          active-theming:base-title
          active-theming:base-workspace

          active-theming:ignored-titles-regexp
          active-theming:host-regexp
          active-theming:user-regexp

          active-theming-initialise
          active-theming-deinitialise
          active-theming-refresh
          active-theming-refresh-all

          active-theming-current-value
          
          active-theming-method-focus
          active-theming-method-group
          active-theming-method-host
          active-theming-method-position
          active-theming-method-root
          active-theming-method-sticky
          active-theming-method-title
          active-theming-method-workspace
          )

  (open rep
        rep.data.tables
        rep.io.processes
        rep.regexp
        rep.system
        sawfish.wm
        hsv
        string2
        )


(define (deferred-refresh) (active-theming-refresh-all))

(defgroup active-theming "Active-Theming"
  :group appearance)

(defcustom active-theming:base-color (hsv-to-color 200 0.5 0.6)
  "Base colour for active-theming calculations."
  :type color
  :group (appearance active-theming)
  :user-level novice
  :after-set deferred-refresh)

(defcustom active-theming:far-color (hsv-to-color 330 1.0 0.85)
  "The colour at the far end of the active-theming calculations."
  :type color
  :group (appearance active-theming)
  :user-level novice
  :after-set deferred-refresh)

(defcustom active-theming:model  'HSV
  "The colour model to be used for active-theming calculations."
  :type (set HSV RGB)
  :group (appearance active-theming)
  :user-level novice
  :after-set deferred-refresh)

(defcustom active-theming:primary-method  'host
  "The method used to change the hue / red component."
  :type (set focus
             group
             host
             position
             root
             sticky
             title
             workspace)
  :group (appearance active-theming)
  :user-level novice
  :after-set deferred-refresh)

(defcustom active-theming:secondary-method  'root
  "The method used to change the saturation / green component."
  :type (set focus
             group
             host
             position
             root
             sticky
             title
             workspace)
  :group (appearance active-theming)
  :user-level novice
  :after-set deferred-refresh)

(defcustom active-theming:tertiary-method  'focus
  "The method used to change the value / blue component."
  :type (set focus
             group
             host
             position
             root
             sticky
             title
             workspace)
  :group (appearance active-theming)
  :user-level novice
  :after-set deferred-refresh)

(defcustom active-theming:primary-hash-all t
  "Whether to return values across the whole axis when hashing on the primary
axis."
  :type boolean
  :group (appearance active-theming)
  :user-level expert
  :after-set deferred-refresh)

(defcustom active-theming:secondary-hash-all t
  "Whether to return values across the whole axis when hashing on the secondary
axis."
  :type boolean
  :group (appearance active-theming)
  :user-level expert
  :after-set deferred-refresh)

(defcustom active-theming:tertiary-hash-all t
  "Whether to return values across the whole axis when hashing on the tertiary
axis."
  :type boolean
  :group (appearance active-theming)
  :user-level expert
  :after-set deferred-refresh)

(defcustom active-theming:base-host
  (let* ((str (make-string-output-stream))
         (process (make-process str)))
    (call-process process nil "uname" "-n")
   (string2-trim (get-output-stream-string str)))
  "The host name that will be given the base colour by the host method."
  :type string
  :group (appearance active-theming)
  :user-level expert
  :after-set deferred-refresh)

(defcustom active-theming:base-position "+0+0"
  "The position that will be given the base colour by the position method."
  :type string
  :group (appearance active-theming)
  :user-level expert
  :after-set deferred-refresh)

(defcustom active-theming:base-title ""
  "The window title that will be given the base colour by the title method."
  :type string
  :group (appearance active-theming)
  :user-level expert
  :after-set deferred-refresh)

(defcustom active-theming:base-workspace 0
  "The workspace that will be given the base colour by the workspace method."
  :type number
  :group (appearance active-theming)
  :user-level expert
  :after-set deferred-refresh)

(defcustom active-theming:ignored-titles-regexp
  "(^((This IS window )|(0\\$)))|(^$)"
  "Regular expression used to recognise whether a window title change should
be ignored.  This is used to keep window colours constant when given transient
messages (from the program 'screen', for example)."
  :type string
  :group (appearance active-theming)
  :user-level expert
  :after-set deferred-refresh)

(defcustom active-theming:user-regexp
  "(.*)@"
  "Regular expression used to extract the current username from terminal
titles."
  :type string
  :group (appearance active-theming)
  :user-level expert
  :after-set deferred-refresh)

(defcustom active-theming:host-regexp
  "@(.*):"
  "Regular expression used to extract the current hostname from terminal
titles."
  :type string
  :group (appearance active-theming)
  :user-level expert
  :after-set deferred-refresh)


(define (color-property active)
  (if active
      'frame-active-color
    'frame-inactive-color)  
  )

(define (get-color window active)
  (window-get window (color-property active))
  )

(define (put-color window active color)
  (window-put window (color-property active) color)
  )


(define (using-hsv)
  (= active-theming:model 'HSV)
  )
  

(define (color-component axis color)
  (case axis
    ((hue)        (nth 0 (color-to-hsv color)))
    ((saturation) (nth 1 (color-to-hsv color)))
    ((value)      (nth 2 (color-to-hsv color)))
    ((red)        (nth 0 (color-rgb color)))
    ((green)      (nth 1 (color-rgb color)))
    ((blue)       (nth 2 (color-rgb color)))
    )
  )


(define (component axis far)
  (color-component axis (if far
                             active-theming:far-color
                           active-theming:base-color))
  )


(define (calc-color w active methods axes conv)
  (let ((c1 ((nth 0 methods) w active (nth 0 axes)))
        (c2 ((nth 1 methods) w active (nth 1 axes)))
        (c3 ((nth 2 methods) w active (nth 2 axes))))
    (conv c1 c2 c3)
    )
  )


(define (calc-colors-given-axes w axes conv)
  (let ((methods (mapcar method '(1 2 3))))
    (list
     (calc-color w nil methods axes conv)
     (calc-color w t   methods axes conv)
     )
    )
  )


(define (calc-colors w)
  (if (using-hsv)
      (calc-colors-given-axes w '(hue saturation value) hsv-to-color)
    (calc-colors-given-axes w '(red green blue) get-color-rgb)
    )
  )


(define (set-colors w)
  (let* ((colors (calc-colors w))
         (normal (car colors))
         (active (cadr colors)))
    (put-color w nil normal)
    (put-color w t active)
    nil
    )
  )


(define (determine-method m)
  (let ((f (eval m)))
    (cond
     ((functionp f) f)
     ((symbolp f)
      (let ((f (eval (intern (concat "active-theming-method-"
                                     (prin1-to-string f))))))
        (if (functionp f)
            f
          (error (concat "Value given for " (prin1-to-string m)
                         " is not a function")))))))
  )


(define (method n)
  (case n
    ((1) (determine-method 'active-theming:primary-method))
    ((2) (determine-method 'active-theming:secondary-method))
    ((3) (determine-method 'active-theming:tertiary-method))
    )
  )


(define (axis-range axis)
  (case axis
    ((hue)                360)
    ((saturation value)     1)
    ((red green blue)   65536)
    )
  )


(define (hashing-modulus axis)
  "Return the appropriate hashing modulus for the given axis.  Hue is an
integer on [0, 360), and red, green, and blue are integers on [0, 65536).
These axes get a moduli matching their ranges.  Saturation and value are
floating point numbers on [0, 1], so we give these a modulus matching their
divisor of 65536."
  (case axis
    ((hue)                360)
    ((saturation value) 65536)
    ((red green blue)   65536)
    )
  )


(define (hashing-divisor axis)
  "Return the appropriate hashing divisor for the given axis.  Hue is an
integer on [0, 360), and red, green, and blue are integers on [0, 65536).
These axes get a divisor of 1, being integers, and hashing-modulus handles the
range issue.  Saturation and value are floating point numbers on [0, 1], so we
give these a divisor matching their modulus of 65536.  This gives a 16-bit
precision in the fractional part, which is fine."
  (case axis
    ((hue)                  1)
    ((saturation value) 65536)
    ((red green blue)       1)
    )
  )


(define (hashing-full axis)
  (case axis
    ((hue red)          active-theming:primary-hash-all)
    ((saturation green) active-theming:secondary-hash-all)
    ((value blue)       active-theming:tertiary-hash-all)
    )
  )


(define (component-by-hashing data-string zero-string axis)
  "Determine the component on the given axis as a hash of the given
data-string.  The hash is computed such that the value would be equal to the
appropriate component of base-color if data-string were equal to the given
zero-string."

  (when (null data-string)
    (error "data-string is null in component-by-hashing"))
  (when (null zero-string)
    (error "zero-string is null in component-by-hashing"))

  (if (hashing-full axis)
      (component-by-hashing-full data-string zero-string axis)
    (component-by-hashing-partial data-string zero-string axis)
  )
)


(define (component-by-hashing-full data-string zero-string axis)
  "The rep function string-hash is used for the actual hashing.  The hash is
offset so that if data-string equalled zero-string, the result would be the
appropriate component of base-color.  Finally, the result is taken modulo
(hashing-modulus axis) and divided by (hashing-divisor axis).  This last
operation brings the hash result (a 32-bit integer value) into the range and
precision required for the given axis."

  (let* ((m (hashing-modulus axis))
         (d (hashing-divisor axis))
         (hash-offset (inexact->exact
                       (round
                        (* (- (component axis nil)
                              (string-hash zero-string))
                           d))))
         (u (mod (+ (string-hash data-string) hash-offset) m))
         (v (if (< u 0)
                (+ u m)
              u))
         )
    (if (= d 1)
        (inexact->exact (round v))
      (/ v d)
      )
    )
  )


(define (component-by-hashing-partial data-string zero-string axis)
  "Unlike component-by-hashing-full, our result must map only onto the axis
portion defined by the difference between far-color and base-color.  To achieve
this, the hash is first offset so that if data-string equalled zero-string, the
result would be the 0.  The result is taken modulo (hashing-modulus axis) *
range-in-use / (axis-range axis) and then divided by (hashing-divisor axis).
Finally, the base-color component is added to offset the result appropriately."

  (let* ((m (hashing-modulus axis))
         (d (hashing-divisor axis))
         (b (component axis nil))
         (f (component axis t))
         (range-in-use (- f b))
         (range-total (axis-range axis))
         (modulus (inexact->exact
                   (round
                    (/ (* m range-in-use) range-total))))
         (hash-offset (inexact->exact
                       (round
                        (* (- 0
                              (string-hash zero-string))
                           d))))
         (u (mod (+ (string-hash data-string) hash-offset) modulus))
         (v (if (< u 0)
                (+ u modulus)
              u))
         (w (if (= d 1)
                (inexact->exact (round v))
              (/ v d)))
         )
    (+ w b)
    )
  )


(define (active-theming-refresh w)
  (set-colors w)
  (rebuild-frame w)
  )


(define (active-theming-refresh-all)
  (mapc active-theming-refresh (managed-windows))
  )


(define (active-theming-current-value w active axis)
  "Return the current value of the component appropriate for the given
parameters."
  (let ((color (or (get-color w active)
                   active-theming:base-color)))
    (color-component axis color)
    )
  )


(define (window-property-changed w an)
  (when (or (eq an 'WM_NAME) (eq an 'workspaces))
    (active-theming-refresh w))
  )


(define (active-theming-initialise)
  (add-hook 'property-notify-hook window-property-changed)
  (add-hook 'add-window-hook set-colors)
  (add-hook 'add-to-workspace-hook active-theming-refresh)
  (add-hook 'window-moved-hook active-theming-refresh)
  (add-hook 'workspace-state-change-hook active-theming-refresh-all)
  (active-theming-refresh-all)
  )


(define (active-theming-deinitialise)
  (remove-hook 'property-notify-hook window-property-changed)
  (remove-hook 'add-window-hook set-colors)
  (remove-hook 'add-to-workspace-hook active-theming-refresh)
  (remove-hook 'window-moved-hook active-theming-refresh)
  (remove-hook 'workspace-state-change-hook active-theming-refresh-all)
  nil
  )


;;;; Focus Method

(define (active-theming-method-focus w active axis)
  (declare (unused w))
  
  (component axis active)
  )


;;;; Host Method

(define (active-theming-method-host w active axis)
  (let ((name (window-name w)))
    (if (string-match active-theming:ignored-titles-regexp name)
        (active-theming-current-value w active axis)
      (let ((host 
             (if (string-match active-theming:host-regexp name)
                 (substring name (match-start 1) (match-end 1))
               (let ((client (nth 2 (get-x-property w 'WM_CLIENT_MACHINE))))
                 (or client active-theming:base-host nil)))))
        
        (component-by-hashing host active-theming:base-host axis)
        )
      )
    )
  )

;;;; Group Method

(define (active-theming-method-group w active axis)
  (declare (unused active))

  (component-by-hashing (prin1-to-string (window-group-id w)) "0" axis)
  )


;;;; Root Method

(define (active-theming-method-root w active axis)
  (let ((name (window-name w)))
    (if (string-match active-theming:ignored-titles-regexp name)
        (active-theming-current-value w active axis)

      (component axis
                 (when (string-match active-theming:user-regexp name)
                   (string-equal "root"
                                 (substring name
                                            (match-start 1)
                                            (match-end 1))))))
    )
  )


;;;; Position Method

(define (pos->string x)
  (let ((xs (number->string x)))
    (if (<= x 0)
        (concat "+" xs)
      xs))
  )


(define (active-theming-method-position w active axis)
  (declare (unused active))

  (let ((position (window-position w)))
    (component-by-hashing (concat (pos->string (car position))
                                  (pos->string (cdr position)))
                          active-theming:base-position
                          axis)
    )
  )


;;;; Sticky Method

(define (active-theming-method-sticky w active axis)
  (declare (unused active))

  (component axis (window-get w 'sticky))
  )


;;;; Title Method

(define (active-theming-method-title w active axis)
  (let ((name (window-name w)))
    (if (string-match active-theming:ignored-titles-regexp name)
        (active-theming-current-value w active axis)
      (component-by-hashing name active-theming:base-title axis)
      )
    )
  )


;;;; Workspace Method

(define (workspace-name ws)
  (let* ((limits (workspace-limits))
         (first-workspace (or (car limits) 0))
         (last-workspace (or (cdr limits) 0)))
    (if (or (null ws)
            (> ws last-workspace)
            (< ws first-workspace))
        "Invalid Workspace"
      (or
       (nth (- ws first-workspace) workspace-names)
       (format nil "Workspace %d" ws))
      )
    )
  )


(define (string-reverse s)
  (let loop ((result "")
             (pos 0))
       (if (>= pos (length s))
           result
         (loop (concat (elt s pos) result) (1+ pos)))
       )
  )


(define (reverse-workspace-name ws)
  (string-reverse (workspace-name ws))
  )

  
(define (active-theming-method-workspace w active axis)
  (declare (unused active))

  (let ((workspace (car (window-workspaces w))))
    (component-by-hashing (reverse-workspace-name
                           (or workspace active-theming:base-workspace))
                          (reverse-workspace-name
                           active-theming:base-workspace)
                          axis)
    )
  )


)
