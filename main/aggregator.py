import pymysql
import math

#TODO: Calculate evenness, numerator is done need to get S
#      Implement multi species aggregates- probably use person_id to work out if classifications made by same person, then get median number of species ID'd

#Creates aggregate classification for a photo whose ID is passed to the function. atm passing the connection to the database from outside but probably want to make the connection inside the function for the final implementation
def aggregate_classifications(photo_ID,connection):
    c=connection.cursor()

    #Getting id for blank and useless (don't know/other) options
    sql="SELECT option_id FROM options where struc='noanimal'"
    c.execute(sql)
    blankOptionSelect=c.fetchall()
    blankOptions=[]
    for n in blankOptionSelect:
        blankOptions.append(n['option_id'])

    sql="SELECT option_id FROM options where struc='notinlist'"
    c.execute(sql)
    uselessOptionSelect=c.fetchall()
    uselessOptions=[]
    for n in uselessOptionSelect:
        uselessOptions.append(n['option_id'])

    #Get all rows for given photo ID except for useless classifications
    #May not need to get some fields? (animal id, timestamp?)
    sql="SELECT * FROM animal where photo_id="+str(photo_ID)
    for i in uselessOptions:
        sql+=" AND species!="+str(i)
    c.execute(sql)
    result=c.fetchall()
    
    speciesTally=dict()
    numClass=0
    nonBlanks=0
    for row in result:
        rowS=row['species']
        numClass+=1
        if rowS not in blankOptions:
            nonBlanks+=1
        
        if  rowS in speciesTally:
            speciesTally[rowS]=speciesTally[rowS]+1
        else:
            speciesTally[rowS]=1

    if numClass>0:
        values=list(speciesTally.values())
        keys=list(speciesTally.keys())
        
        #If two species have the same number of classifications, the first one in the list is taken. How to resolve this properly doesn't seem to be in the paper?
        species=keys[values.index(max(values))]

        fractionSupport=speciesTally[species]/numClass

        if species not in blankOptions:
            #No need to do calculations if support is 1
            if fractionSupport==1:
                evenness=0
                fractionBlanks=0
            else:
                print(speciesTally)
                fractionBlanks=1-(nonBlanks/numClass)

                n=0
                numSpecies=0
                for s in speciesTally:
                    print(s)
                    if s not in blankOptions:
                        numSpecies+=1
                        print(numSpecies)
                        ps=speciesTally[s]/nonBlanks
                        n-=ps*math.log(ps)
                evenness=n/numSpecies
        else:
            fractionBlanks=1
            #Paper doesn't say what to do in this case, but because evenness is calculated with non-blanks it amkes sense to do this
            evenness=-1                    
    else:
        #This only happens if no classifications that aren't useless
        #Would need to add an unclassified option to database
        #Kinda nice to have all fields here to more easily see everything that needs doing
        species=-1
        evenness=-1
        fractionBlanks=-1
        fractionSupport=-1
    #Test print
    print(photo_ID,species,evenness,fractionBlanks,fractionSupport,numClass)
    
        
    sql="INSERT INTO nameofaggregateclasstablehere (%s,%s,%s,etc)"
    #c.execute(sql,(values (species, evenness etc) here))
    #c.commit()

connection = pymysql.connect(host='localhost',user='root',password='toot',db='mammalwebdump',charset='utf8mb4',cursorclass=pymysql.cursors.DictCursor)
for id in range(20000):
    aggregate_classifications(id,connection)

connection.close()
