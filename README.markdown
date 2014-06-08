[![Build Status](https://travis-ci.org/cakephp/cakepackages.svg?branch=master)](https://travis-ci.org/cakephp/cakepackages)

# CakePackages

A self-contained application that tracks CakePHP developer's open source code repositories, including applications and plugins. Includes some social integration features

##  Requirements

- PHP 5.3+

## Installation

```bash
git clone git://github.com/cakephp/cakepackages.git cakepackages
cd cakepackages && git submodule update --init
```

## Usage

You'll want to run the migrations for both cakepackages and the plugins. I've also included a schema.php file. SQL Dump with dummy data to come.

## Todo

- Documentation
- Increase visibility across the web through social integration
- Integrate with GitHub's OAuth implementation for user accounts
- Push user accounts and a user dashboard
- Possible dashboard aggregating both code and community resources useful to that person?
- Integrate a [risingcake.com](risingcake.com) like news dashboard
- Integrate a project's readme
- More robust application/plugin distinction, as well as checking both standards and to see if something that should be a plugin is
- Allow users to submit tags for a package
- Unit tests
- Infinite pagination of packages

## License

Copyright (c) 2009-2012 Jose Diaz-Gonzalez

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
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
THE SOFTWARE.
