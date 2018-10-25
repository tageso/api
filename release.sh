#!/bin/bash

echo Version: $1

docCheck=`grep "^# 2.0.0$" docs/docs/version.md | wc -l`
if [ $docCheck -eq 0 ]
then
    echo "Version not in docs/docs/version.md"
    exit
fi

gitCheck=`git diff | wc -l`
if [ $gitCheck -ne 0 ]
then
    echo "Open Git Changed"
    exit
fi

echo "Start Release in 10 Secounds";
exit
# Update Version
currentDate=`date +%d.%m.%Y`

sed -i "s/# 2.0.0/# 2.0.0 ($currentDate)/" docs/docs/version.md

rm -r -v VERSION
echo $1 > VERSION

echo "DO RELEASE"

git add VERSION
git add docs/docs/version.md
git commit -m "Release Version $1"
git push origin master

git tag -a $1 -m "$1"
git push origin $1

git rm -r -f VERSION
git commit -a -m "Remove Versions File for next Development"
git push origin master

echo "Release Done"
