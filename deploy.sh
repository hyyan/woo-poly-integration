#!/usr/bin/env bash
rm -rf build

# prepare files to upload
rsync -avh . ./build --exclude-from '.exclude-list' --delete

# change to build
cd build

# Checkout the SVN repo
svn co -q "http://svn.wp-plugins.org/woo-poly-integration" svn

# Move out the trunk directory to a temp location
#mv ./svn/trunk ./svn-trunk

# Create trunk directory
mkdir -p svn/trunk

# Copy our new version of the plugin into trunk
rsync -r -p ./* svn/trunk

# # Copy all the .svn folders from the checked out copy of trunk to the new trunk.
# # This is necessary as the Travis container runs Subversion 1.6 which has .svn dirs in every sub dir
# cd svn/trunk/
# TARGET=$(pwd)
# cd ../../svn-trunk/

# # Find all .svn dirs in sub dirs
# SVN_DIRS=`find . -type d -iname .svn`

# for SVN_DIR in $SVN_DIRS; do
#     SOURCE_DIR=${SVN_DIR/.}
#     TARGET_DIR=$TARGET${SOURCE_DIR/.svn}
#     TARGET_SVN_DIR=$TARGET${SVN_DIR/.}
#     if [ -d "$TARGET_DIR" ]; then
#         # Copy the .svn directory to trunk dir
#         cp -r $SVN_DIR $TARGET_SVN_DIR
#     fi
# done

# # Back to builds dir
# cd ../

# Remove checked out dir
#rm -fR svn-trunk

# Add new version tag
#mkdir -p ./svn/tags/$TRAVIS_TAG
#rsync -r -p . svn/tags/$TRAVIS_TAG

# Add new files to SVN
svn stat svn | grep '^?' | awk '{print $2}' | xargs -I x svn add x@
# Remove deleted files from SVN
svn stat svn | grep '^!' | awk '{print $2}' | xargs -I x svn rm --force x@
svn stat svn

# Commit to SVN
svn ci --no-auth-cache --username hyyan --password $WP_ORG_PASSWORD svn -m "build version $TRAVIS_TAG"

